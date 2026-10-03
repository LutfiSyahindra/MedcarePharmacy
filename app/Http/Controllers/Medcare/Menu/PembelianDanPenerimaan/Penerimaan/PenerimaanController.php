<?php

namespace App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Penerimaan;

use App\Http\Controllers\Controller;
use App\Models\MasterObatModel;
use App\Models\Menu\Keuangan\FinanceTransactionModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Services\Menu\Keuangan\FinanceService;
use App\Services\Menu\Stok\StockService;
use App\Services\Notifikasi\TransactionNotificationService;
use App\Services\Settings\Margins\MarginsService;
use App\Support\BranchAccess;
use App\Support\TieredDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanController extends Controller
{
    private const RECEIVABLE_PO_STATUSES = ['approved', 'diterima_sebagian'];

    public function __construct(
        private readonly StockService $stockService,
        private readonly TransactionNotificationService $transactionNotifications,
        private readonly MarginsService $marginsService,
        private readonly FinanceService $financeService,
    ) {}

    public function penerimaan(Request $request)
    {
        $request->validate([
            'purchase_order_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $selectedPurchaseOrder = null;
        $initialReceiptId = null;

        if ($request->filled('purchase_order_id')) {
            $query = PembelianModel::query();
            $this->scopePurchaseOrderBranch($query);
            $selectedPurchaseOrder = $query->findOrFail($request->integer('purchase_order_id'));
            $initialReceiptId = $selectedPurchaseOrder->penerimaanBarang()
                ->whereIn('status', ['draft', 'posted'])
                ->orderByRaw("CASE WHEN status = 'draft' THEN 0 ELSE 1 END")
                ->latest('tanggal_penerimaan')
                ->latest('id')
                ->value('id');
        }

        return view('medcare.menu.pembelianPenerimaan.penerimaan.penerimaan', compact(
            'selectedPurchaseOrder',
            'initialReceiptId'
        ));
    }

    public function table(Request $request)
    {
        $request->validate([
            'purchase_order_id' => ['nullable', 'integer', 'min:1'],
            'obat_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = PenerimaanBarangModel::with(['purchaseOrder', 'distributor', 'createdBy'])
            ->latest('tanggal_penerimaan')
            ->latest('id');

        $this->scopePenerimaanBranch($query);

        if ($request->filled('purchase_order_id')) {
            $query->where('purchase_order_id', $request->integer('purchase_order_id'));
        }

        if ($request->filled('obat_id')) {
            $medicineId = $request->integer('obat_id');
            $query->whereHas('details', fn ($details) => $details->where('obat_id', $medicineId))
                ->with(['details' => fn ($details) => $details
                    ->where('obat_id', $medicineId)
                    ->with(['obat.satuan', 'purchaseOrderDetail.satuanKonversi.satuan'])
                    ->orderBy('id')]);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('tanggal_penerimaan', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('tanggal_penerimaan', '<=', $request->date_end);
        }

        $penerimaan = $query->get();
        $activePenerimaan = $penerimaan->where('status', '!=', 'cancelled');

        $summary = [
            'total' => $penerimaan->count(),
            'draft' => $penerimaan->where('status', 'draft')->count(),
            'posted' => $penerimaan->where('status', 'posted')->count(),
            'cancelled' => $penerimaan->where('status', 'cancelled')->count(),
            'total_qty' => $activePenerimaan->sum('total_qty'),
            'grand_total' => $activePenerimaan->sum('grand_total'),
        ];
        $approvalUser = Auth::user();

        return DataTables::of($penerimaan)
            ->addIndexColumn()
            ->addColumn('no_po', fn ($row) => $row->purchaseOrder->no_po ?? '-')
            ->addColumn('supplier', fn ($row) => $row->distributor->nama ?? '-')
            ->addColumn('tanggal', fn ($row) => optional($row->tanggal_penerimaan)->format('Y-m-d'))
            ->addColumn('user', fn ($row) => $row->createdBy->name ?? '-')
            ->addColumn('obat_dipilih_details', function ($row) use ($request) {
                if (! $request->filled('obat_id')) {
                    return [];
                }

                return $row->details->map(fn ($detail) => [
                    'obat_id' => $detail->obat_id,
                    'nama_obat' => $detail->obat->nama_obat ?? '-',
                    'kode_obat' => $detail->obat->kode_obat ?? '-',
                    'qty_diterima' => (float) $detail->qty_diterima,
                    'satuan' => $detail->satuan_beli
                        ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama ?? ($detail->obat->satuan->nama ?? '-')),
                    'no_batch' => $detail->no_batch,
                    'expired_date' => optional($detail->expired_date)->format('Y-m-d'),
                ])->values()->all();
            })
            ->addColumn('actions', function ($row) use ($approvalUser) {
                $canApprove = $this->transactionNotifications->canApproveBranch(
                    $approvalUser,
                    $row->purchaseOrder?->branch_id ? (int) $row->purchaseOrder->branch_id : null
                );
                $detailButton = '<button class="btn btn-sm btn-info" onclick="lihatPenerimaan('.$row->id.')"><i class="mdi mdi-eye"></i></button>';
                $printButton = '<a class="btn btn-sm btn-secondary btn-print-receipt" href="'.route('penerimaan.print', $row->id).'" target="_blank" rel="noopener"><i class="mdi mdi-printer-outline"></i></a>';
                $editButton = $row->status === 'draft'
                    ? '<button class="btn btn-sm btn-success" onclick="editPenerimaan('.$row->id.')"><i class="mdi mdi-pencil"></i></button>'
                    : '';
                $postButton = $canApprove && $row->status === 'draft'
                    ? '<button class="btn btn-sm btn-primary" onclick="postPenerimaan('.$row->id.')"><i class="mdi mdi-send-check-outline"></i></button>'
                    : '';
                $cancelButton = $canApprove && $row->status !== 'cancelled'
                    ? '<button class="btn btn-sm btn-warning" onclick="cancelPenerimaan('.$row->id.')"><i class="mdi mdi-cancel"></i></button>'
                    : '';
                $deleteButton = $row->status === 'draft'
                    ? '<button class="btn btn-sm btn-danger" onclick="deletePenerimaan('.$row->id.')"><i class="mdi mdi-delete"></i></button>'
                    : '';

                return $detailButton.' '.$printButton.' '.$editButton.' '.$postButton.' '.$cancelButton.' '.$deleteButton;
            })
            ->removeColumn('details')
            ->rawColumns(['actions'])
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function receiptMedicines(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'purchase_order_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $receiptMedicineIds = PenerimaanBarangDetailModel::query()
            ->select('obat_id')
            ->whereHas('penerimaanBarang', function ($receipts) use ($request) {
                $this->scopePenerimaanBranch($receipts);

                if ($request->filled('purchase_order_id')) {
                    $receipts->where('purchase_order_id', $request->integer('purchase_order_id'));
                }
            });

        $query = MasterObatModel::query()
            ->select(['id', 'nama_obat', 'kode_obat'])
            ->whereIn('id', $receiptMedicineIds);
        $search = trim((string) $request->input('q', ''));

        if ($search !== '') {
            $query->where(function ($medicines) use ($search) {
                $medicines->where('nama_obat', 'like', '%'.$search.'%')
                    ->orWhere('kode_obat', 'like', '%'.$search.'%');
            });
        }

        $medicines = $query->orderBy('nama_obat')->orderBy('id')->simplePaginate(20);

        return response()->json([
            'results' => $medicines->getCollection()->map(fn ($medicine) => [
                'id' => $medicine->id,
                'text' => $medicine->nama_obat.' ('.$medicine->kode_obat.')',
                'nama_obat' => $medicine->nama_obat,
                'kode_obat' => $medicine->kode_obat,
            ])->values(),
            'pagination' => ['more' => $medicines->hasMorePages()],
        ]);
    }

    public function generateNoPenerimaan()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'PB-'.$year.'-'.$month.'-';

        $last = PenerimaanBarangModel::where('nomor_penerimaan', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = $last ? (int) substr($last->nomor_penerimaan, -4) : 0;

        return response()->json($prefix.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT));
    }

    public function approvedPurchaseOrders()
    {
        $query = PembelianModel::with(['distributor', 'branch', 'details.obat.satuan', 'details.satuanKonversi.satuan'])
            ->whereIn('status', self::RECEIVABLE_PO_STATUSES)
            ->latest('tanggal_po')
            ->latest('id');

        $this->scopePurchaseOrderBranch($query);

        $orders = $query->get()
            ->map(function ($po) {
                $totalOutstanding = 0;
                $outstandingItems = 0;
                $medicines = [];

                foreach ($po->details as $detail) {
                    $outstanding = max(0, round((float) $detail->qty - $this->receivedQtyForPoDetail($detail->id), 2));
                    $totalOutstanding += $outstanding;
                    $outstandingItems += $outstanding > 0 ? 1 : 0;
                    $medicines[] = [
                        'id' => $detail->obat_id,
                        'nama_obat' => $detail->obat->nama_obat ?? '-',
                        'kode_obat' => $detail->obat->kode_obat ?? '-',
                        'satuan' => $detail->satuanKonversi->satuan->nama ?? ($detail->obat->satuan->nama ?? '-'),
                        'qty_po' => (float) $detail->qty,
                        'outstanding_qty' => $outstanding,
                    ];
                }

                return [
                    'id' => $po->id,
                    'no_po' => $po->no_po,
                    'text' => $po->no_po.' - '.($po->distributor->nama ?? 'Supplier tidak diketahui'),
                    'supplier' => $po->distributor->nama ?? '-',
                    'branch' => $po->branch->name ?? '-',
                    'status' => $po->status,
                    'tanggal_po' => $po->tanggal_po,
                    'total_estimasi' => (float) $po->total_estimasi,
                    'item_count' => $po->details->count(),
                    'outstanding_items' => $outstandingItems,
                    'outstanding_qty' => $totalOutstanding,
                    'medicines' => $medicines,
                ];
            })
            ->filter(fn ($po) => $po['outstanding_qty'] > 0)
            ->values();

        return response()->json($orders);
    }

    public function purchaseOrderDetail($id)
    {
        return response()->json($this->purchaseOrderPayload($id));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        return DB::transaction(function () use ($request) {
            $po = $this->approvedPo($request->purchase_order_id);
            $computed = $this->buildComputedDetails($request, $po);
            $compensationPlan = $this->buildSupplierCompensationPlan($request, $po, $computed);

            $penerimaan = PenerimaanBarangModel::create($this->headerPayload(
                $request,
                $po,
                $computed,
                $compensationPlan['total']
            ));

            foreach ($computed['details'] as $detail) {
                $penerimaan->details()->create($detail);
            }

            foreach ($compensationPlan['allocations'] as $allocation) {
                $penerimaan->supplierCompensationAllocations()->create($allocation);
            }

            $creator = Auth::user();
            DB::afterCommit(fn () => $this->transactionNotifications->notifyApprovalRequest('penerimaan', $penerimaan, $creator));

            return response()->json([
                'status' => 'success',
                'message' => 'Draft penerimaan barang berhasil disimpan.',
            ]);
        });
    }

    public function show($id)
    {
        return response()->json($this->penerimaanPayload($id, false));
    }

    public function printReceipt(Request $request, $id)
    {
        $penerimaan = $this->penerimaanQueryForBranch([
            'purchaseOrder.branch.apotekProfile',
            'distributor',
            'details.obat.satuan',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
            'createdBy',
            'postedBy',
            'cancelledBy',
        ])->findOrFail($id);

        return view('medcare.menu.pembelianPenerimaan.penerimaan.print', [
            'penerimaan' => $penerimaan,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function edit($id)
    {
        $penerimaan = $this->penerimaanQueryForBranch()->findOrFail($id);

        if ($penerimaan->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya penerimaan berstatus draft yang bisa diedit.',
            ], 422);
        }

        return response()->json($this->penerimaanPayload($id));
    }

    public function hargaJualPreview(Request $request, $id)
    {
        $validated = $request->validate([
            'diskon_untuk' => ['nullable', 'in:pasien,apotek'],
        ]);

        $penerimaan = PenerimaanBarangModel::with([
            'purchaseOrder',
            'distributor',
            'details.obat.satuan',
            'details.obat.golongan',
            'details.obat.mainGolongan',
            'details.obat.subGolongan',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
        ]);

        $this->scopePenerimaanBranch($penerimaan);

        $penerimaan = $penerimaan->findOrFail($id);

        if ($penerimaan->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Harga jual hanya bisa dipreview dari penerimaan berstatus draft.',
            ], 422);
        }

        return response()->json($this->sellingPricePayload(
            $penerimaan,
            $validated['diskon_untuk'] ?? 'pasien'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules($id));

        return DB::transaction(function () use ($request, $id) {
            $penerimaan = $this->penerimaanQueryForBranch([
                'details',
                'supplierCompensationAllocations',
            ])->findOrFail($id);

            if ($penerimaan->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya penerimaan berstatus draft yang bisa diedit.',
                ]);
            }

            $po = $this->approvedPo($request->purchase_order_id);
            $computed = $this->buildComputedDetails($request, $po, $penerimaan->id);
            $compensationPlan = $this->buildSupplierCompensationPlan($request, $po, $computed);

            $payload = $this->headerPayload(
                $request,
                $po,
                $computed,
                $compensationPlan['total']
            );
            unset($payload['created_by']);

            $penerimaan->update($payload);
            $penerimaan->details()->delete();
            $penerimaan->supplierCompensationAllocations()->delete();

            foreach ($computed['details'] as $detail) {
                $penerimaan->details()->create($detail);
            }

            foreach ($compensationPlan['allocations'] as $allocation) {
                $penerimaan->supplierCompensationAllocations()->create($allocation);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Draft penerimaan barang berhasil diperbarui.',
            ]);
        });
    }

    public function post(Request $request, $id)
    {
        if (! $this->transactionNotifications->isApprovalRole(Auth::user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki akses approval untuk penerimaan.',
            ], 403);
        }

        $validated = $request->validate([
            'diskon_untuk' => ['required', 'in:pasien,apotek'],
            'payment_method' => ['nullable', Rule::in(array_keys(FinanceService::SETTLEMENT_PAYMENT_METHODS))],
            'payment_occurred_at' => ['nullable', 'date'],
            'payment_reference_no' => ['nullable', 'string', 'max:120'],
            'payment_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'diskon_untuk.required' => 'Pilih diskon diberikan ke pasien atau diambil apotek.',
            'diskon_untuk.in' => 'Pilihan penerima diskon tidak valid.',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $penerimaan = $this->penerimaanQueryForBranch([
                'purchaseOrder',
                'details.obat.satuan',
                'details.obat.golongan',
                'details.obat.mainGolongan',
                'details.obat.subGolongan',
                'details.purchaseOrderDetail.satuanKonversi.satuan',
                'supplierCompensationAllocations.returPembelian.compensations',
            ], BranchAccess::approvalBranchIds())->lockForUpdate()->findOrFail($id);

            if ($penerimaan->status !== 'draft') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya draft penerimaan yang bisa diposting.',
                ], 422);
            }

            $initialPaymentAmount = round(max(0, (float) $penerimaan->jumlah_dibayar), 2);

            if ($initialPaymentAmount > 0.009 && empty($validated['payment_method'])) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Pilih metode pembayaran agar pembayaran faktur dapat dicatat ke Keuangan.',
                ]);
            }

            $penerimaan->forceFill([
                'diskon_untuk' => $validated['diskon_untuk'],
            ])->save();
            $this->applyDiscountRecipientToStockCosts($penerimaan, $validated['diskon_untuk']);

            $this->finalizeSupplierCompensationDiscount($penerimaan);

            $sellingPricesByDetailId = $this->sellingPriceRowsByDetailId($penerimaan);
            $postedAt = now();

            // A draft may originate from a previously cancelled receipt during recovery/rework.
            // Refresh its posting metadata before stock movements are recorded so the ledger
            // never reuses an old posted_at value or retains stale cancellation metadata.
            $penerimaan->forceFill([
                'posted_by' => Auth::id(),
                'posted_at' => $postedAt,
                'cancelled_by' => null,
                'cancelled_at' => null,
            ])->save();

            foreach ($penerimaan->details as $detail) {
                $this->stockService->recordReceipt(
                    $penerimaan,
                    $detail,
                    $sellingPricesByDetailId[$detail->id]['harga_jual'] ?? null
                );
            }

            $sellingPrices = array_values($sellingPricesByDetailId);

            $penerimaan->update([
                'status' => 'posted',
            ]);

            $financeTransaction = null;

            if ($initialPaymentAmount > 0.009) {
                $financeTransaction = $this->financeService->recordInitialSupplierPayment(
                    Auth::user(),
                    (int) $penerimaan->id,
                    [
                        'payment_method' => $validated['payment_method'],
                        'occurred_at' => $validated['payment_occurred_at'] ?? now(),
                        'reference_no' => $validated['payment_reference_no'] ?? null,
                        'notes' => $validated['payment_notes'] ?? null,
                    ]
                );
            }

            $this->syncPurchaseOrderReceivingStatus($penerimaan->purchase_order_id);
            $actor = Auth::user();
            DB::afterCommit(fn () => $this->transactionNotifications->notifyActionResult('penerimaan', $penerimaan, 'posted', $actor));

            return response()->json([
                'status' => 'success',
                'message' => $financeTransaction
                    ? 'Penerimaan berhasil diposting, stok diperbarui, dan pembayaran faktur tercatat di Keuangan.'
                    : ((float) $penerimaan->supplier_compensation_discount > 0
                        ? 'Penerimaan berhasil diposting, stok diperbarui, dan potongan ganti rugi supplier direalisasikan.'
                        : 'Penerimaan berhasil diposting, stok obat diperbarui, dan harga jual batch tersimpan.'),
                'diskon_untuk' => $penerimaan->diskon_untuk,
                'selling_prices' => $sellingPrices,
                'finance_transaction_number' => $financeTransaction?->number,
            ]);
        });
    }

    public function cancel($id)
    {
        if (! $this->transactionNotifications->isApprovalRole(Auth::user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki akses approval untuk penerimaan.',
            ], 403);
        }

        return DB::transaction(function () use ($id) {
            $penerimaan = $this->penerimaanQueryForBranch([
                'details.obat',
                'supplierCompensationAllocations.compensation',
            ], BranchAccess::approvalBranchIds())->lockForUpdate()->findOrFail($id);

            if ($penerimaan->status === 'cancelled') {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Penerimaan sudah dibatalkan.',
                ]);
            }

            if ($penerimaan->status === 'posted') {
                $hasSupplierPayments = FinanceTransactionModel::query()
                    ->where('source_type', 'supplier_payable')
                    ->where('source_id', $penerimaan->id)
                    ->where('status', 'posted')
                    ->exists();

                if ($hasSupplierPayments) {
                    throw ValidationException::withMessages([
                        'status' => 'Penerimaan tidak dapat dibatalkan karena memiliki pembayaran supplier aktif. Batalkan jurnal pembayarannya melalui Buku Kas terlebih dahulu.',
                    ]);
                }

                foreach ($penerimaan->details as $detail) {
                    $this->stockService->reverseReceipt($penerimaan, $detail);
                }

                $this->reverseSupplierCompensationDiscount($penerimaan);
            }

            $penerimaan->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            $this->syncPurchaseOrderReceivingStatus($penerimaan->purchase_order_id);
            $actor = Auth::user();
            DB::afterCommit(fn () => $this->transactionNotifications->notifyActionResult('penerimaan', $penerimaan, 'cancelled', $actor));

            return response()->json([
                'status' => 'success',
                'message' => 'Penerimaan berhasil dibatalkan.',
            ]);
        });
    }

    public function destroy($id)
    {
        $penerimaan = $this->penerimaanQueryForBranch()->findOrFail($id);

        if ($penerimaan->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya draft penerimaan yang bisa dihapus.',
            ], 422);
        }

        $penerimaan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Draft penerimaan berhasil dihapus.',
        ]);
    }

    private function rules(?int $ignoreId = null): array
    {
        $unique = 'unique:penerimaan_barang,nomor_penerimaan';

        if ($ignoreId) {
            $unique .= ','.$ignoreId;
        }

        return [
            'nomor_penerimaan' => ['required', 'string', 'max:60', $unique],
            'purchase_order_id' => ['required', 'integer', 'exists:purchase_orders,id'],
            'nomor_faktur' => ['required', 'string', 'max:100'],
            'nomor_surat_jalan' => ['nullable', 'string', 'max:100'],
            'tanggal_penerimaan' => ['required', 'string'],
            'tanggal_faktur' => ['required', 'string'],
            'tanggal_jatuh_tempo' => ['nullable', 'string'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'pajak' => ['nullable', 'numeric', 'min:0'],
            'biaya_lain' => ['nullable', 'numeric', 'min:0'],
            'total_faktur' => ['nullable', 'numeric', 'min:0'],
            'supplier_compensation_discount' => ['nullable', 'numeric', 'min:0'],
            'jumlah_dibayar' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
            'purchase_order_detail_id' => ['required', 'array'],
            'purchase_order_detail_id.*' => ['required', 'integer', 'exists:purchase_order_details,id'],
            'obat_id' => ['required', 'array'],
            'obat_id.*' => ['required', 'integer', 'exists:master_obats,id'],
            'qty_diterima' => ['required', 'array'],
            'qty_diterima.*' => ['required', 'numeric', 'min:0'],
            'stok_batch_id' => ['nullable', 'array'],
            'stok_batch_id.*' => ['nullable', 'integer', 'exists:stok_batches,id'],
            'no_batch' => ['required', 'array'],
            'no_batch.*' => ['nullable', 'string', 'max:80'],
            'expired_date' => ['required', 'array'],
            'expired_date.*' => ['nullable', 'string'],
            'harga_beli' => ['required', 'array'],
            'harga_beli.*' => ['required', 'numeric', 'min:0'],
            'ppn' => ['required', 'array'],
            'ppn.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    private function approvedPo($id): PembelianModel
    {
        $query = PembelianModel::with([
            'distributor',
            'details.obat.satuan',
            'details.satuanKonversi.satuan',
        ]);

        $this->scopePurchaseOrderBranch($query);

        $po = $query->lockForUpdate()->findOrFail($id);

        if (! in_array($po->status, self::RECEIVABLE_PO_STATUSES, true)) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'Pilih PO yang sudah disetujui atau masih diterima sebagian.',
            ]);
        }

        return $po;
    }

    private function buildComputedDetails(Request $request, PembelianModel $po, ?int $ignorePenerimaanId = null): array
    {
        $detailsById = $po->details->keyBy('id');
        $branchId = (int) $po->branch_id;
        $rows = [];
        $summary = [
            'total_barang' => 0,
            'total_qty' => 0,
            'subtotal' => 0,
            'total_diskon' => 0,
            'total_ppn' => 0,
            'grand_total' => 0,
        ];

        $quantitiesByDetail = [];

        foreach ($request->purchase_order_detail_id as $index => $poDetailId) {
            $poDetail = $detailsById->get((int) $poDetailId);

            if (! $poDetail) {
                throw ValidationException::withMessages([
                    'purchase_order_detail_id.'.$index => 'Item tidak sesuai dengan PO yang dipilih.',
                ]);
            }

            $qty = (float) ($request->qty_diterima[$index] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $selectedBatch = $this->selectedStockBatch(
                $request->input('stok_batch_id.'.$index),
                (int) $poDetail->obat_id,
                $index,
                $branchId
            );
            $batch = trim((string) ($request->no_batch[$index] ?? ''));
            $expired = trim((string) ($request->expired_date[$index] ?? ''));

            if ($selectedBatch) {
                $batch = $selectedBatch->no_batch;
                $expired = optional($selectedBatch->expired_date)->format('Y-m-d') ?: '';
            }

            if ($batch === '') {
                throw ValidationException::withMessages(['no_batch.'.$index => 'Nomor batch wajib diisi.']);
            }

            if ($expired === '' && ! $selectedBatch) {
                throw ValidationException::withMessages(['expired_date.'.$index => 'Expired date wajib diisi.']);
            }

            $outstanding = max(0, round((float) $poDetail->qty - $this->receivedQtyForPoDetail($poDetail->id, $ignorePenerimaanId), 2));

            $quantitiesByDetail[$poDetail->id] = ($quantitiesByDetail[$poDetail->id] ?? 0) + $qty;

            if ($quantitiesByDetail[$poDetail->id] - $outstanding > 0.00001) {
                throw ValidationException::withMessages([
                    'qty_diterima.'.$index => 'Qty diterima melebihi sisa PO ('.$outstanding.').',
                ]);
            }

            $harga = (float) ($request->harga_beli[$index] ?? 0);
            $conversion = $this->conversionFactor($poDetail);
            $qtyStock = $qty * $conversion;
            $hargaStock = $conversion > 0 ? $harga / $conversion : $harga;
            [$diskon1, $diskon2, $diskon3] = TieredDiscount::percentages(
                $poDetail->diskon_1,
                $poDetail->diskon_2,
                $poDetail->diskon_3
            );
            $diskon = TieredDiscount::effectivePercentage($diskon1, $diskon2, $diskon3);
            $ppn = $this->discountPercent($request->ppn[$index] ?? 0);
            $subtotal = round($qty * $harga, 2);
            $taxBase = TieredDiscount::netAmount($subtotal, $diskon1, $diskon2, $diskon3);
            $nilaiDiskon = round($subtotal - $taxBase, 2);
            $nilaiPpn = round($taxBase * ($ppn / 100), 2);
            $total = round($taxBase + $nilaiPpn, 2);

            $rows[] = [
                'purchase_order_detail_id' => $poDetail->id,
                'obat_id' => (int) $poDetail->obat_id,
                'qty_po' => $poDetail->qty,
                'qty_diterima' => $qty,
                'qty_diterima_stok' => $qtyStock,
                'konversi_satuan' => $conversion,
                'satuan_beli' => $this->purchaseUnitLabel($poDetail),
                'satuan_stok' => $this->stockUnitLabel($poDetail),
                'stok_batch_id' => $this->stockBatchIdForDiscountAndTax($selectedBatch, $diskon, $ppn),
                'no_batch' => $batch,
                'expired_date' => $expired === '' ? null : $this->parseDate($expired),
                'harga_beli' => $harga,
                'harga_beli_stok' => $hargaStock,
                'diskon_1' => $diskon1,
                'diskon_2' => $diskon2,
                'diskon_3' => $diskon3,
                'diskon' => $diskon,
                'ppn' => $ppn,
                'subtotal' => $subtotal,
                'nilai_diskon' => $nilaiDiskon,
                'nilai_ppn' => $nilaiPpn,
                'total' => $total,
            ];

            $summary['total_barang']++;
            $summary['total_qty'] += $qty;
            $summary['subtotal'] += $subtotal;
            $summary['total_diskon'] += $nilaiDiskon;
            $summary['total_ppn'] += $nilaiPpn;
            $summary['grand_total'] += $total;
        }

        if (count($rows) === 0) {
            throw ValidationException::withMessages([
                'qty_diterima' => 'Isi minimal satu qty diterima lebih dari 0.',
            ]);
        }

        return ['details' => $rows] + $summary;
    }

    private function buildSupplierCompensationPlan(
        Request $request,
        PembelianModel $po,
        array $computed
    ): array {
        $requestedDiscount = $this->moneyValue($request->supplier_compensation_discount);

        if ($requestedDiscount <= 0) {
            return ['total' => 0, 'allocations' => []];
        }

        $grossTotalFaktur = $this->moneyValue(
            (float) $computed['subtotal']
                - (float) $computed['total_diskon']
                + (float) $computed['total_ppn']
                + $this->allocatedPurchaseOrderAdditionalCost($po, (float) $computed['total_qty'])
        );

        if ($requestedDiscount > $grossTotalFaktur + 0.009) {
            throw ValidationException::withMessages([
                'supplier_compensation_discount' => 'Potongan ganti rugi tidak boleh melebihi total tagihan sebelum potongan.',
            ]);
        }

        $openReturns = $this->openSupplierCompensationReturns((int) $po->distributor_id);
        $available = round((float) $openReturns->sum('compensation_outstanding_value'), 2);

        if ($requestedDiscount > $available + 0.009) {
            throw ValidationException::withMessages([
                'supplier_compensation_discount' => 'Potongan melebihi saldo ganti rugi supplier sebesar Rp '.number_format($available, 0, ',', '.').'.',
            ]);
        }

        $remaining = $requestedDiscount;
        $allocations = [];

        foreach ($openReturns as $retur) {
            if ($remaining <= 0.009) {
                break;
            }

            $nominal = min($remaining, (float) $retur->compensation_outstanding_value);

            if ($nominal <= 0) {
                continue;
            }

            $allocations[] = [
                'retur_pembelian_id' => $retur->id,
                'nominal' => round($nominal, 2),
            ];
            $remaining = round($remaining - $nominal, 2);
        }

        if ($remaining > 0.009) {
            throw ValidationException::withMessages([
                'supplier_compensation_discount' => 'Saldo ganti rugi supplier berubah. Muat ulang PO dan periksa kembali nominal potongan.',
            ]);
        }

        return [
            'total' => $requestedDiscount,
            'allocations' => $allocations,
        ];
    }

    private function finalizeSupplierCompensationDiscount(PenerimaanBarangModel $penerimaan): void
    {
        $discount = (float) $penerimaan->supplier_compensation_discount;

        if ($discount <= 0) {
            return;
        }

        $allocations = $penerimaan->supplierCompensationAllocations
            ->sortBy('retur_pembelian_id')
            ->values();
        $allocatedTotal = round((float) $allocations->sum('nominal'), 2);

        if (abs($allocatedTotal - $discount) > 0.009) {
            throw ValidationException::withMessages([
                'supplier_compensation_discount' => 'Alokasi potongan ganti rugi tidak sesuai dengan total potongan penerimaan.',
            ]);
        }

        $branchIds = BranchAccess::userBranchIds();

        foreach ($allocations as $allocation) {
            $retur = ReturPembelianModel::with('compensations')
                ->where('id', $allocation->retur_pembelian_id)
                ->where('distributor_id', $penerimaan->distributor_id)
                ->where('status', 'posted')
                ->where('expects_compensation', true)
                ->whereHas('purchaseOrder', function ($query) use ($branchIds) {
                    $this->scopePurchaseOrderBranch($query, $branchIds);
                })
                ->lockForUpdate()
                ->first();

            if (! $retur) {
                throw ValidationException::withMessages([
                    'supplier_compensation_discount' => 'Retur sumber potongan tidak lagi tersedia atau tidak dapat diakses.',
                ]);
            }

            $nominal = (float) $allocation->nominal;

            if ($nominal > $retur->compensation_outstanding_value + 0.009) {
                throw ValidationException::withMessages([
                    'supplier_compensation_discount' => 'Saldo ganti rugi '.$retur->nomor_retur.' berubah. Perbarui draft penerimaan sebelum posting.',
                ]);
            }

            $compensation = $retur->compensations()->create([
                'penerimaan_barang_id' => $penerimaan->id,
                'tanggal_realisasi' => optional($penerimaan->tanggal_faktur ?: $penerimaan->tanggal_penerimaan)->format('Y-m-d'),
                'jenis' => 'potongan_faktur',
                'nominal' => $nominal,
                'nomor_referensi' => $penerimaan->nomor_penerimaan,
                'nomor_faktur' => $penerimaan->nomor_faktur,
                'keterangan' => 'Potongan langsung pada faktur penerimaan '.$penerimaan->nomor_penerimaan.'.',
                'created_by' => Auth::id(),
            ]);

            $allocation->update([
                'retur_pembelian_compensation_id' => $compensation->id,
            ]);
        }
    }

    private function reverseSupplierCompensationDiscount(PenerimaanBarangModel $penerimaan): void
    {
        foreach ($penerimaan->supplierCompensationAllocations as $allocation) {
            $compensation = $allocation->compensation;

            if (! $compensation || $compensation->cancelled_at) {
                continue;
            }

            $compensation->update([
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
                'cancellation_reason' => 'Dibatalkan otomatis karena penerimaan '.$penerimaan->nomor_penerimaan.' dibatalkan.',
            ]);
        }
    }

    private function headerPayload(
        Request $request,
        PembelianModel $po,
        array $computed,
        float $supplierCompensationDiscount = 0
    ): array {
        $subtotal = $this->moneyValue($computed['subtotal']);
        $diskon = $this->moneyValue($computed['total_diskon']);
        $pajak = $this->moneyValue($computed['total_ppn']);
        $biayaLain = $this->allocatedPurchaseOrderAdditionalCost($po, (float) $computed['total_qty']);
        $grossTotalFaktur = $this->moneyValue($subtotal - $diskon + $pajak + $biayaLain);
        $supplierCompensationDiscount = min(
            $this->moneyValue($supplierCompensationDiscount),
            $grossTotalFaktur
        );
        $tagihanSetelahGantiRugi = $this->moneyValue($grossTotalFaktur - $supplierCompensationDiscount);
        $jumlahDibayar = $this->moneyValue($request->jumlah_dibayar);

        if ($jumlahDibayar > $tagihanSetelahGantiRugi + 0.009) {
            throw ValidationException::withMessages([
                'jumlah_dibayar' => 'Nominal pembayaran tidak boleh melebihi tagihan bersih sebesar Rp '.number_format($tagihanSetelahGantiRugi, 0, ',', '.').'.',
            ]);
        }

        $jumlahDibayar = min($jumlahDibayar, $tagihanSetelahGantiRugi);
        $sisaHutang = $this->moneyValue($tagihanSetelahGantiRugi - $jumlahDibayar);

        return [
            'nomor_penerimaan' => $request->nomor_penerimaan,
            'purchase_order_id' => $po->id,
            'distributor_id' => $po->distributor_id,
            'nomor_faktur' => $request->nomor_faktur,
            'nomor_surat_jalan' => $request->nomor_surat_jalan,
            'tanggal_penerimaan' => $this->parseDate($request->tanggal_penerimaan),
            'tanggal_faktur' => $this->parseNullableDate($request->tanggal_faktur, 'tanggal_faktur'),
            'tanggal_jatuh_tempo' => $this->parseNullableDate($request->tanggal_jatuh_tempo, 'tanggal_jatuh_tempo'),
            'total_barang' => $computed['total_barang'],
            'total_qty' => $computed['total_qty'],
            'subtotal' => $subtotal,
            'total_diskon' => $diskon,
            'total_ppn' => $pajak,
            'grand_total' => $grossTotalFaktur,
            'diskon' => $diskon,
            'pajak' => $pajak,
            'biaya_lain' => $biayaLain,
            'supplier_compensation_discount' => $supplierCompensationDiscount,
            'total_faktur' => $grossTotalFaktur,
            'status_pembayaran' => $this->paymentStatus($tagihanSetelahGantiRugi, $jumlahDibayar),
            'jumlah_dibayar' => $jumlahDibayar,
            'sisa_hutang' => $sisaHutang,
            'catatan' => $request->catatan,
            'created_by' => Auth::id(),
        ];
    }

    private function penerimaanPayload($id, bool $requireReceivablePo = true): array
    {
        $penerimaan = $this->penerimaanQueryForBranch([
            'purchaseOrder.distributor',
            'distributor',
            'details.obat',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
            'createdBy',
            'postedBy',
            'cancelledBy',
        ])->findOrFail($id);

        return [
            'header' => $penerimaan,
            'po_payload' => $this->purchaseOrderPayload(
                $penerimaan->purchase_order_id,
                $penerimaan->id,
                $requireReceivablePo
            ),
        ];
    }

    private function purchaseOrderPayload(
        $poId,
        ?int $ignorePenerimaanId = null,
        bool $requireReceivableStatus = true
    ): array {
        $query = PembelianModel::with([
            'distributor',
            'branch',
            'details.obat',
            'details.obat.satuan',
            'details.satuanKonversi.satuan',
        ]);

        $this->scopePurchaseOrderBranch($query);

        $po = $query->findOrFail($poId);

        if ($requireReceivableStatus && ! in_array($po->status, self::RECEIVABLE_PO_STATUSES, true)) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'PO belum disetujui atau sudah selesai.',
            ]);
        }

        $batchOptionsByObat = $this->stockBatchOptionsByObat($po->details->pluck('obat_id')->all(), (int) $po->branch_id);

        $details = $po->details->map(function ($detail) use ($ignorePenerimaanId, $batchOptionsByObat) {
            $received = $this->receivedQtyForPoDetail($detail->id, $ignorePenerimaanId);
            $outstanding = max(0, round((float) $detail->qty - $received, 2));

            return [
                'id' => $detail->id,
                'obat_id' => $detail->obat_id,
                'nama_obat' => $detail->obat->nama_obat ?? '-',
                'kode_obat' => $detail->obat->kode_obat ?? '-',
                'satuan' => $detail->satuanKonversi->satuan->nama ?? ($detail->obat->satuan->nama ?? '-'),
                'satuan_stok' => $detail->obat->satuan->nama ?? 'PCS',
                'konversi' => $this->conversionFactor($detail),
                'qty_po' => (float) $detail->qty,
                'received_qty' => $received,
                'outstanding_qty' => $outstanding,
                'qty_po_stok' => (float) $detail->qty * $this->conversionFactor($detail),
                'received_qty_stok' => $received * $this->conversionFactor($detail),
                'outstanding_qty_stok' => $outstanding * $this->conversionFactor($detail),
                'harga_estimasi' => (float) $detail->harga_estimasi,
                'diskon_1' => (float) ($detail->diskon_1 ?? 0),
                'diskon_2' => (float) ($detail->diskon_2 ?? 0),
                'diskon_3' => (float) ($detail->diskon_3 ?? 0),
                'ppn' => $detail->ppn !== null ? (float) $detail->ppn : 11,
                'diskon_efektif' => TieredDiscount::effectivePercentage(
                    $detail->diskon_1,
                    $detail->diskon_2,
                    $detail->diskon_3
                ),
                'harga_estimasi_stok' => $this->conversionFactor($detail) > 0
                    ? (float) $detail->harga_estimasi / $this->conversionFactor($detail)
                    : (float) $detail->harga_estimasi,
                'batch_options' => $batchOptionsByObat[(int) $detail->obat_id] ?? [],
            ];
        })->values();

        $poDetailsById = $details->keyBy('id');
        $receivedInvoices = $po->penerimaanBarang()
            ->with('details.obat')
            ->where('status', '!=', 'cancelled')
            ->when($ignorePenerimaanId, fn ($query) => $query->where('id', '!=', $ignorePenerimaanId))
            ->orderBy('tanggal_penerimaan')
            ->orderBy('id')
            ->get()
            ->map(function ($receipt) use ($poDetailsById) {
                return [
                    'id' => $receipt->id,
                    'nomor_penerimaan' => $receipt->nomor_penerimaan,
                    'nomor_faktur' => $receipt->nomor_faktur,
                    'tanggal_faktur' => optional($receipt->tanggal_faktur)->format('Y-m-d'),
                    'tanggal_penerimaan' => optional($receipt->tanggal_penerimaan)->format('Y-m-d'),
                    'status' => $receipt->status,
                    'details' => $receipt->details->map(function ($detail) use ($poDetailsById) {
                        $poDetail = $poDetailsById->get($detail->purchase_order_detail_id);

                        return [
                            'purchase_order_detail_id' => $detail->purchase_order_detail_id,
                            'obat_id' => $detail->obat_id,
                            'nama_obat' => $detail->obat->nama_obat ?? '-',
                            'kode_obat' => $detail->obat->kode_obat ?? '-',
                            'qty_diterima' => (float) $detail->qty_diterima,
                            'satuan' => $detail->satuan_beli ?: ($poDetail['satuan'] ?? '-'),
                            'no_batch' => $detail->no_batch,
                            'expired_date' => optional($detail->expired_date)->format('Y-m-d'),
                        ];
                    })->values(),
                ];
            })->values();

        return [
            'id' => $po->id,
            'no_po' => $po->no_po,
            'status' => $po->status,
            'can_create_receipt' => in_array($po->status, self::RECEIVABLE_PO_STATUSES, true)
                && $details->sum('outstanding_qty') > 0,
            'supplier' => $po->distributor->nama ?? '-',
            'distributor_id' => $po->distributor_id,
            'branch' => $po->branch->name ?? '-',
            'tanggal_po' => $po->tanggal_po,
            'total_estimasi' => $po->total_estimasi,
            'biaya_asuransi' => $this->moneyValue($po->biaya_asuransi),
            'biaya_pengiriman' => $this->moneyValue($po->biaya_pengiriman),
            'total_biaya_tambahan' => $this->purchaseOrderAdditionalCost($po),
            'total_qty_po' => round((float) $po->details->sum('qty'), 4),
            'catatan' => $po->catatan,
            'supplier_compensation_alert' => $this->supplierCompensationAlert((int) $po->distributor_id),
            'details' => $details,
            'received_invoices' => $receivedInvoices,
        ];
    }

    private function supplierCompensationAlert(int $distributorId): array
    {
        $branchIds = BranchAccess::userBranchIds();

        if ($distributorId <= 0 || empty($branchIds)) {
            return [
                'has_outstanding' => false,
                'return_count' => 0,
                'overdue_count' => 0,
                'outstanding_value' => 0,
                'returns' => [],
            ];
        }

        $sortedReturns = $this->openSupplierCompensationReturns($distributorId, $branchIds);

        return [
            'has_outstanding' => $sortedReturns->isNotEmpty(),
            'return_count' => $sortedReturns->count(),
            'overdue_count' => $sortedReturns->where('compensation_status', 'overdue')->count(),
            'outstanding_value' => round((float) $sortedReturns->sum('compensation_outstanding_value'), 2),
            'returns' => $sortedReturns
                ->take(10)
                ->map(fn (ReturPembelianModel $retur) => [
                    'id' => $retur->id,
                    'nomor_retur' => $retur->nomor_retur,
                    'tanggal_retur' => optional($retur->tanggal_retur)->format('Y-m-d'),
                    'due_date' => optional($retur->compensation_due_date)->format('Y-m-d'),
                    'expected_value' => $retur->compensation_expected_value,
                    'received_value' => $retur->compensation_received_value,
                    'outstanding_value' => $retur->compensation_outstanding_value,
                    'status' => $retur->compensation_status,
                    'branch' => $retur->purchaseOrder?->branch?->name ?? '-',
                    'notes' => $retur->compensation_notes,
                ])
                ->all(),
        ];
    }

    private function openSupplierCompensationReturns(int $distributorId, ?array $branchIds = null)
    {
        $branchIds = $branchIds ?? BranchAccess::userBranchIds();

        if ($distributorId <= 0 || empty($branchIds)) {
            return collect();
        }

        return ReturPembelianModel::with(['compensations', 'purchaseOrder.branch'])
            ->where('distributor_id', $distributorId)
            ->where('status', 'posted')
            ->where('expects_compensation', true)
            ->whereHas('purchaseOrder', function ($query) use ($branchIds) {
                $this->scopePurchaseOrderBranch($query, $branchIds);
            })
            ->get()
            ->filter(fn (ReturPembelianModel $retur) => in_array(
                $retur->compensation_status,
                ['waiting', 'partial', 'overdue'],
                true
            ))
            ->sortBy(fn (ReturPembelianModel $retur) => implode('-', [
                $retur->compensation_status === 'overdue' ? '0' : '1',
                optional($retur->compensation_due_date)->format('Ymd') ?: '99999999',
                optional($retur->tanggal_retur)->format('Ymd') ?: '99999999',
                str_pad((string) $retur->id, 20, '0', STR_PAD_LEFT),
            ]))
            ->values();
    }

    private function receivedQtyForPoDetail($poDetailId, ?int $ignorePenerimaanId = null): float
    {
        return (float) PenerimaanBarangDetailModel::where('purchase_order_detail_id', $poDetailId)
            ->whereHas('penerimaanBarang', function ($query) use ($ignorePenerimaanId) {
                $query->where('status', '!=', 'cancelled');

                if ($ignorePenerimaanId) {
                    $query->where('id', '!=', $ignorePenerimaanId);
                }
            })
            ->sum('qty_diterima');
    }

    private function selectedStockBatch($batchId, int $obatId, int $index, int $branchId): ?StokBatchModel
    {
        if ($batchId === null || $batchId === '') {
            return null;
        }

        $batch = StokBatchModel::where('id', $batchId)
            ->where('obat_id', $obatId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $batch) {
            throw ValidationException::withMessages([
                'stok_batch_id.'.$index => 'Batch stok tidak sesuai dengan obat yang dipilih.',
            ]);
        }

        return $batch;
    }

    private function stockBatchOptionsByObat(array $obatIds, int $branchId): array
    {
        $obatIds = collect($obatIds)
            ->filter()
            ->unique()
            ->values();

        if ($obatIds->isEmpty()) {
            return [];
        }

        return StokBatchModel::whereIn('obat_id', $obatIds)
            ->where('branch_id', $branchId)
            ->orderBy('expired_date')
            ->orderBy('no_batch')
            ->get()
            ->groupBy('obat_id')
            ->map(fn ($batches) => $batches
                ->map(fn (StokBatchModel $batch) => $this->stockBatchOption($batch))
                ->values()
                ->all())
            ->all();
    }

    private function stockBatchOption(StokBatchModel $batch): array
    {
        $expiredDate = optional($batch->expired_date)->format('Y-m-d');
        $expiredDateLabel = optional($batch->expired_date)->format('d-m-Y');

        return [
            'id' => $batch->id,
            'text' => $batch->no_batch
                .' | ED '.($expiredDateLabel ?: '-')
                .' | Diskon '.number_format((float) ($batch->diskon ?? 0), 2, ',', '.').'%'
                .' | PPN '.number_format((float) ($batch->ppn ?? 0), 2, ',', '.').'%'
                .' | HPP Rp '.number_format((float) $batch->harga_beli, 2, ',', '.')
                .' | Stok '.number_format((float) $batch->qty, 2, ',', '.'),
            'no_batch' => $batch->no_batch,
            'expired_date' => $expiredDate,
            'qty' => (float) $batch->qty,
            'harga_beli' => (float) $batch->harga_beli,
            'biaya_lain' => (float) ($batch->biaya_lain ?? 0),
            'harga_jual' => (float) $batch->harga_jual,
            'diskon' => (float) ($batch->diskon ?? 0),
            'ppn' => (float) ($batch->ppn ?? 0),
        ];
    }

    private function stockBatchIdForDiscountAndTax(?StokBatchModel $batch, float $diskon, float $ppn): ?int
    {
        if (! $batch) {
            return null;
        }

        $sameDiscount = $this->samePercent((float) ($batch->diskon ?? 0), $diskon);
        $sameTax = $this->samePercent((float) ($batch->ppn ?? 0), $ppn);

        return $sameDiscount && $sameTax ? $batch->id : null;
    }

    private function discountPercent($value): float
    {
        return round(min(100, max(0, (float) ($value ?: 0))), 2);
    }

    private function moneyValue($value): float
    {
        return round(max(0, (float) ($value ?: 0)), 2);
    }

    private function paymentStatus(float $tagihan, float $jumlahDibayar): string
    {
        if ($tagihan <= 0.009) {
            return 'lunas';
        }

        if ($jumlahDibayar <= 0.009) {
            return 'belum_dibayar';
        }

        return $jumlahDibayar >= $tagihan - 0.009 ? 'lunas' : 'sebagian';
    }

    private function purchaseOrderAdditionalCost(PembelianModel $po): float
    {
        return $this->moneyValue(
            $this->moneyValue($po->biaya_asuransi) + $this->moneyValue($po->biaya_pengiriman)
        );
    }

    private function allocatedPurchaseOrderAdditionalCost(PembelianModel $po, float $receivedQty): float
    {
        $additionalCost = $this->purchaseOrderAdditionalCost($po);
        $orderedQty = (float) $po->details->sum('qty');

        if ($additionalCost <= 0 || $orderedQty <= 0 || $receivedQty <= 0) {
            return 0;
        }

        return $this->moneyValue($additionalCost * min($receivedQty, $orderedQty) / $orderedQty);
    }

    private function sameDiscount(float $left, float $right): bool
    {
        return $this->samePercent($left, $right);
    }

    private function samePercent(float $left, float $right): bool
    {
        return abs($this->discountPercent($left) - $this->discountPercent($right)) < 0.00001;
    }

    private function postedReceivedQtyForPoDetail($poDetailId): float
    {
        return (float) PenerimaanBarangDetailModel::where('purchase_order_detail_id', $poDetailId)
            ->whereHas('penerimaanBarang', function ($query) {
                $query->where('status', 'posted');
            })
            ->sum('qty_diterima');
    }

    private function syncPurchaseOrderReceivingStatus($poId): void
    {
        $po = PembelianModel::with('details')->lockForUpdate()->find($poId);

        if (! $po || ! in_array($po->status, ['approved', 'diterima_sebagian', 'selesai'], true)) {
            return;
        }

        $totalPoQty = 0;
        $totalReceivedQty = 0;

        foreach ($po->details as $detail) {
            $totalPoQty += (float) $detail->qty;
            $totalReceivedQty += $this->postedReceivedQtyForPoDetail($detail->id);
        }

        $nextStatus = 'approved';

        if ($totalReceivedQty > 0 && $totalReceivedQty < $totalPoQty) {
            $nextStatus = 'diterima_sebagian';
        }

        if ($totalPoQty > 0 && $totalReceivedQty >= $totalPoQty) {
            $nextStatus = 'selesai';
        }

        if ($po->status !== $nextStatus) {
            $po->status = $nextStatus;
            $po->save();
        }
    }

    private function applyDiscountRecipientToStockCosts(
        PenerimaanBarangModel $penerimaan,
        string $diskonUntuk
    ): void {
        $otherCostAllocations = $this->otherCostAllocationsByDetailId($penerimaan);

        foreach ($penerimaan->details as $detail) {
            $allocatedOtherCost = $otherCostAllocations[$detail->id] ?? 0;
            $qtyStock = $this->detailStockQuantity($detail, $this->detailConversionFactor($detail));
            $otherCostPerStockUnit = $qtyStock > 0
                ? round($allocatedOtherCost / $qtyStock, 2)
                : 0;

            $detail->forceFill([
                'harga_beli_stok' => $this->detailStockPurchasePrice(
                    $detail,
                    $diskonUntuk
                ),
                'alokasi_biaya_lain' => $allocatedOtherCost,
                'biaya_lain_stok' => $otherCostPerStockUnit,
            ])->save();
        }
    }

    private function sellingPricePayload(
        PenerimaanBarangModel $penerimaan,
        ?string $diskonUntuk = null
    ): array {
        $diskonUntuk = $diskonUntuk ?: ($penerimaan->diskon_untuk ?: 'pasien');
        $otherCostAllocations = $this->otherCostAllocationsByDetailId($penerimaan);
        $details = $penerimaan->details
            ->map(fn (PenerimaanBarangDetailModel $detail) => $this->sellingPriceRow(
                $detail,
                $otherCostAllocations[$detail->id] ?? 0,
                $diskonUntuk
            ))
            ->values();

        return [
            'header' => [
                'id' => $penerimaan->id,
                'nomor_penerimaan' => $penerimaan->nomor_penerimaan,
                'no_po' => $penerimaan->purchaseOrder->no_po ?? '-',
                'supplier' => $penerimaan->distributor->nama ?? '-',
                'tanggal_penerimaan' => optional($penerimaan->tanggal_penerimaan)->format('Y-m-d'),
                'biaya_lain' => round((float) $penerimaan->biaya_lain, 2),
                'nomor_faktur' => $penerimaan->nomor_faktur,
                'jumlah_dibayar' => round((float) $penerimaan->jumlah_dibayar, 2),
                'diskon_untuk' => $diskonUntuk,
                'diskon_untuk_label' => $diskonUntuk === 'pasien' ? 'Diberikan ke Pasien' : 'Diambil Apotek',
            ],
            'payment_methods' => FinanceService::SETTLEMENT_PAYMENT_METHODS,
            'details' => $details,
            'summary' => [
                'item_count' => $details->count(),
                'missing_margin_count' => $details->where('has_margin', false)->count(),
            ],
        ];
    }

    private function sellingPriceRowsByDetailId(PenerimaanBarangModel $penerimaan): array
    {
        $diskonUntuk = $penerimaan->diskon_untuk ?: 'pasien';
        $otherCostAllocations = $this->otherCostAllocationsByDetailId($penerimaan);

        return $penerimaan->details
            ->mapWithKeys(fn (PenerimaanBarangDetailModel $detail) => [
                $detail->id => $this->sellingPriceRow(
                    $detail,
                    $otherCostAllocations[$detail->id] ?? 0,
                    $diskonUntuk
                ),
            ])
            ->all();
    }

    private function sellingPriceRow(
        PenerimaanBarangDetailModel $detail,
        float $allocatedOtherCost = 0,
        string $diskonUntuk = 'pasien'
    ): array {
        $detail->loadMissing([
            'obat.satuan',
            'obat.golongan',
            'obat.mainGolongan',
            'obat.subGolongan',
            'purchaseOrderDetail.satuanKonversi.satuan',
        ]);

        $obat = $detail->obat;

        if (! $obat) {
            throw ValidationException::withMessages([
                'obat_id' => 'Obat pada detail penerimaan tidak ditemukan.',
            ]);
        }

        $conversion = $this->detailConversionFactor($detail);
        $qtySatuanTerkecil = $this->detailStockQuantity($detail, $conversion);
        $totalHargaBeli = $this->detailPricingPurchaseTotalIncludingTax($detail, $diskonUntuk);
        $hargaBeliStok = $this->detailStockPurchasePrice($detail, $diskonUntuk);
        $ppn = (float) ($detail->ppn ?? 0);
        $diskon = (float) ($detail->diskon ?? 0);
        $diskon1 = (float) ($detail->diskon_1 ?? 0);
        $diskon2 = (float) ($detail->diskon_2 ?? 0);
        $diskon3 = (float) ($detail->diskon_3 ?? 0);
        $margin = $this->marginsService->activeMarginForObat($obat);
        $faktorJual = $margin ? (float) $margin->faktor_jual : 1.0;
        $marginReference = $this->marginsService->marginReferenceLabelForObat($obat, $margin);

        if ($qtySatuanTerkecil <= 0) {
            throw ValidationException::withMessages([
                'qty_diterima' => 'Qty satuan terkecil tidak valid untuk menghitung harga jual.',
            ]);
        }

        $nilaiDiskon = $this->detailDiscountValue($detail, $totalHargaBeli, $diskon);
        $totalHargaJualSebelumBiayaLain = max(0, $totalHargaBeli * $faktorJual);
        $allocatedOtherCost = $this->moneyValue($allocatedOtherCost);
        $totalHargaJual = $totalHargaJualSebelumBiayaLain + $allocatedOtherCost;
        $hargaJual = round($totalHargaJual / $qtySatuanTerkecil, 2);
        $hargaBeliTerkecil = $totalHargaBeli / $qtySatuanTerkecil;
        $biayaLainPerSatuanBeli = $allocatedOtherCost / max(1, (float) $detail->qty_diterima);
        $biayaLainPerSatuanStok = $allocatedOtherCost / $qtySatuanTerkecil;
        $satuanBeli = $detail->satuan_beli
            ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama ?? ($obat->satuan->nama ?? 'satuan'));
        $satuanTerkecil = $detail->satuan_stok ?: ($obat->satuan->nama ?? 'satuan terkecil');

        return [
            'detail_id' => $detail->id,
            'obat_id' => $obat->id,
            'kode_obat' => $obat->kode_obat,
            'nama_obat' => $obat->nama_obat,
            'golongan' => $obat->golongan->nama ?? '-',
            'satuan_beli' => $satuanBeli,
            'satuan_terkecil' => $satuanTerkecil,
            'konversi_satuan' => $conversion,
            'qty_diterima' => round((float) $detail->qty_diterima, 4),
            'qty_satuan_terkecil' => round($qtySatuanTerkecil, 4),
            'harga_beli' => round((float) $detail->harga_beli, 2),
            'harga_beli_stok' => $hargaBeliStok,
            'total_harga_beli' => round($totalHargaBeli, 2),
            'harga_beli_satuan_terkecil' => round($hargaBeliTerkecil, 2),
            'ppn' => $ppn,
            'faktor_jual' => $faktorJual,
            'has_margin' => (bool) $margin,
            'margin_tingkat' => $margin?->tingkat,
            'margin_reference' => $marginReference,
            'diskon' => $diskon,
            'diskon_1' => $diskon1,
            'diskon_2' => $diskon2,
            'diskon_3' => $diskon3,
            'nilai_diskon_beli' => round($nilaiDiskon, 2),
            'nilai_diskon_jual' => $diskonUntuk === 'pasien' ? round($nilaiDiskon, 2) : 0,
            'diskon_untuk' => $diskonUntuk,
            'total_harga_beli_include_ppn' => round($totalHargaBeli, 2),
            'alokasi_biaya_lain' => $allocatedOtherCost,
            'biaya_lain_satuan_beli' => round($biayaLainPerSatuanBeli, 2),
            'biaya_lain_satuan_stok' => round($biayaLainPerSatuanStok, 2),
            'nilai_beli_stok' => round($hargaBeliStok * $qtySatuanTerkecil, 2),
            'total_harga_jual_sebelum_biaya_lain' => round($totalHargaJualSebelumBiayaLain, 2),
            'total_harga_jual' => round($totalHargaJual, 2),
            'harga_jual' => $hargaJual,
        ];
    }

    /**
     * @return array<int, float>
     */
    private function otherCostAllocationsByDetailId(PenerimaanBarangModel $penerimaan): array
    {
        $details = $penerimaan->details
            ->filter(fn (PenerimaanBarangDetailModel $detail) => (float) $detail->qty_diterima > 0)
            ->sortBy('id')
            ->values();
        $totalOtherCost = $this->moneyValue($penerimaan->biaya_lain);
        $totalQty = (float) $details->sum('qty_diterima');

        if ($details->isEmpty() || $totalOtherCost <= 0 || $totalQty <= 0) {
            return [];
        }

        $remainingCost = $totalOtherCost;
        $remainingQty = $totalQty;
        $allocations = [];

        foreach ($details as $index => $detail) {
            $qty = (float) $detail->qty_diterima;
            $isLast = $index === $details->count() - 1;
            $allocation = $isLast
                ? $remainingCost
                : $this->moneyValue($remainingCost * $qty / $remainingQty);

            $allocations[$detail->id] = $allocation;
            $remainingCost = $this->moneyValue($remainingCost - $allocation);
            $remainingQty = max(0, $remainingQty - $qty);
        }

        return $allocations;
    }

    private function detailConversionFactor(PenerimaanBarangDetailModel $detail): float
    {
        $storedConversion = (float) ($detail->konversi_satuan ?? 0);

        if ($storedConversion > 0) {
            return max(1, $storedConversion);
        }

        return max(1, (float) ($detail->purchaseOrderDetail?->satuanKonversi?->konversi ?? 1));
    }

    private function detailStockQuantity(PenerimaanBarangDetailModel $detail, float $conversion): float
    {
        $storedQty = (float) ($detail->qty_diterima_stok ?? 0);

        if ($storedQty > 0) {
            return $storedQty;
        }

        return (float) $detail->qty_diterima * $conversion;
    }

    private function detailTotalPurchasePriceIncludingTax(PenerimaanBarangDetailModel $detail): float
    {
        $storedTotal = (float) ($detail->total ?? 0);

        if ($storedTotal > 0) {
            return $storedTotal;
        }

        $subtotal = (float) ($detail->subtotal ?? ((float) $detail->qty_diterima * (float) $detail->harga_beli));
        $nilaiDiskon = (float) ($detail->nilai_diskon ?? 0);
        $nilaiPpn = (float) ($detail->nilai_ppn ?? 0);

        return max(0, $subtotal - $nilaiDiskon + $nilaiPpn);
    }

    private function detailPricingPurchaseTotalIncludingTax(
        PenerimaanBarangDetailModel $detail,
        string $diskonUntuk
    ): float {
        if ($diskonUntuk === 'pasien') {
            return $this->detailTotalPurchasePriceIncludingTax($detail);
        }

        $subtotal = (float) ($detail->subtotal
            ?? ((float) $detail->qty_diterima * (float) $detail->harga_beli));
        $ppn = $this->discountPercent($detail->ppn ?? 0);

        return round(max(0, $subtotal) * (1 + ($ppn / 100)), 2);
    }

    private function detailStockPurchasePrice(
        PenerimaanBarangDetailModel $detail,
        string $diskonUntuk
    ): float {
        $conversion = $this->detailConversionFactor($detail);
        $qtyStock = $this->detailStockQuantity($detail, $conversion);

        if ($qtyStock <= 0) {
            return 0;
        }

        $totalHargaBeli = max(0, (float) ($detail->subtotal
            ?? ((float) $detail->qty_diterima * (float) $detail->harga_beli)));

        if ($diskonUntuk === 'pasien') {
            $totalHargaBeli = max(
                0,
                $totalHargaBeli - $this->detailDiscountValue(
                    $detail,
                    $totalHargaBeli,
                    (float) ($detail->diskon ?? 0)
                )
            );
        }

        return round($totalHargaBeli / $qtyStock, 2);
    }

    private function detailDiscountValue(PenerimaanBarangDetailModel $detail, float $totalHargaBeli, float $diskon): float
    {
        $storedDiscount = (float) ($detail->nilai_diskon ?? 0);

        if ($storedDiscount > 0) {
            return $storedDiscount;
        }

        $subtotal = (float) ($detail->subtotal ?? ((float) $detail->qty_diterima * (float) $detail->harga_beli));

        if ($subtotal > 0) {
            return TieredDiscount::discountAmount(
                $subtotal,
                $detail->diskon_1,
                $detail->diskon_2,
                $detail->diskon_3
            );
        }

        return $totalHargaBeli * ($diskon / 100);
    }

    private function penerimaanQueryForBranch(array $with = [], ?array $branchIds = null)
    {
        $query = PenerimaanBarangModel::with($with);

        $this->scopePenerimaanBranch($query, $branchIds);

        return $query;
    }

    private function scopePenerimaanBranch($query, ?array $branchIds = null): void
    {
        $branchIds = $branchIds ?? BranchAccess::userBranchIds();

        $query->whereHas('purchaseOrder', function ($purchaseOrderQuery) use ($branchIds) {
            $this->scopePurchaseOrderBranch($purchaseOrderQuery, $branchIds);
        });
    }

    private function scopePurchaseOrderBranch($query, ?array $branchIds = null): void
    {
        $branchIds = $branchIds ?? BranchAccess::userBranchIds();

        if (empty($branchIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('branch_id', $branchIds);
    }

    private function parseNullableDate($date, string $field): ?string
    {
        if (trim((string) $date) === '') {
            return null;
        }

        return $this->parseDate($date, $field);
    }

    private function parseDate($date, string $field = 'tanggal_penerimaan'): string
    {
        $date = trim((string) $date);

        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Format tanggal tidak valid.',
            ]);
        }
    }

    private function conversionFactor($poDetail): float
    {
        return max(1, (float) ($poDetail->satuanKonversi->konversi ?? 1));
    }

    private function purchaseUnitLabel($poDetail): string
    {
        return $poDetail->satuanKonversi->satuan->nama ?? ($poDetail->obat->satuan->nama ?? 'satuan');
    }

    private function stockUnitLabel($poDetail): string
    {
        return $poDetail->obat->satuan->nama ?? 'PCS';
    }
}
