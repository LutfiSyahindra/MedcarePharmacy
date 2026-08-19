<?php

namespace App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\ReturPembelian;

use App\Http\Controllers\Controller;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianCompensationModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Services\Menu\Stok\StockService;
use App\Services\Notifikasi\TransactionNotificationService;
use App\Support\BranchAccess;
use App\Support\TieredDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ReturPembelianController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly TransactionNotificationService $transactionNotifications
    ) {}

    public function returPembelian()
    {
        return view('medcare.menu.pembelianPenerimaan.returPembelian.returPembelian');
    }

    public function table(Request $request)
    {
        $query = ReturPembelianModel::with(['penerimaanBarang', 'purchaseOrder', 'distributor', 'createdBy', 'compensations'])
            ->latest('tanggal_retur')
            ->latest('id');

        $this->scopeReturBranch($query);

        $allPostedRetur = (clone $query)
            ->where('status', 'posted')
            ->get();

        if ($request->filled('date_start')) {
            $query->whereDate('tanggal_retur', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('tanggal_retur', '<=', $request->date_end);
        }

        $retur = $query->get();

        $retur = (match ($request->input('compensation_filter')) {
            'open' => $retur->whereIn('compensation_status', ['waiting', 'partial', 'overdue']),
            'overdue' => $retur->where('compensation_status', 'overdue'),
            'settled' => $retur->where('compensation_status', 'settled'),
            'not_required' => $retur->where('compensation_status', 'not_required'),
            default => $retur,
        })->values();

        $activeRetur = $retur->where('status', '!=', 'cancelled');

        $summary = [
            'total' => $retur->count(),
            'draft' => $retur->where('status', 'draft')->count(),
            'posted' => $retur->where('status', 'posted')->count(),
            'cancelled' => $retur->where('status', 'cancelled')->count(),
            'total_qty' => $activeRetur->sum('total_qty'),
            'grand_total' => $activeRetur->sum('grand_total'),
            'compensation_waiting' => $allPostedRetur
                ->whereIn('compensation_status', ['waiting', 'partial', 'overdue'])
                ->count(),
            'compensation_overdue' => $allPostedRetur
                ->where('compensation_status', 'overdue')
                ->count(),
            'compensation_settled' => $allPostedRetur
                ->where('compensation_status', 'settled')
                ->count(),
            'compensation_not_required' => $allPostedRetur
                ->where('compensation_status', 'not_required')
                ->count(),
            'compensation_outstanding' => $allPostedRetur
                ->sum('compensation_outstanding_value'),
        ];
        $approvalUser = Auth::user();

        return DataTables::of($retur)
            ->addIndexColumn()
            ->addColumn('nomor_penerimaan', fn ($row) => $row->penerimaanBarang->nomor_penerimaan ?? '-')
            ->addColumn('no_po', fn ($row) => $row->purchaseOrder->no_po ?? '-')
            ->addColumn('supplier', fn ($row) => $row->distributor->nama ?? '-')
            ->addColumn('tanggal', fn ($row) => optional($row->tanggal_retur)->format('Y-m-d'))
            ->addColumn('user', fn ($row) => $row->createdBy->name ?? '-')
            ->addColumn('actions', function ($row) use ($approvalUser) {
                $canApprove = $this->transactionNotifications->canApproveBranch(
                    $approvalUser,
                    $row->purchaseOrder?->branch_id ? (int) $row->purchaseOrder->branch_id : null
                );
                $actionButton = function (string $type, string $icon, string $label, string $handler) use ($row): string {
                    return '<button type="button" class="return-action-btn is-'.$type.'" onclick="'.$handler.'('.$row->id.')" data-bs-toggle="tooltip" data-bs-placement="top" title="'.$label.'" aria-label="'.$label.'">
                        <i class="mdi '.$icon.'"></i>
                    </button>';
                };

                $detailButton = $actionButton('detail', 'mdi-eye-outline', 'Lihat detail', 'lihatReturPembelian');
                $compensationButton = $row->status === 'posted'
                    ? $actionButton('compensation', 'mdi-hand-coin-outline', 'Catat ganti rugi supplier', 'kelolaGantiRugi')
                    : '';
                $editButton = $row->status === 'draft'
                    ? $actionButton('edit', 'mdi-pencil-outline', 'Edit draft', 'editReturPembelian')
                    : '';
                $postButton = $canApprove && $row->status === 'draft'
                    ? $actionButton('post', 'mdi-send-check-outline', 'Posting retur', 'postReturPembelian')
                    : '';
                $cancelButton = $canApprove && $row->status !== 'cancelled'
                    ? $actionButton('cancel', 'mdi-cancel', 'Batalkan retur', 'cancelReturPembelian')
                    : '';
                $deleteButton = $row->status === 'draft'
                    ? $actionButton('delete', 'mdi-delete-outline', 'Hapus draft', 'deleteReturPembelian')
                    : '';

                return '<div class="return-action-group" role="group" aria-label="Aksi retur pembelian">'
                    .$detailButton
                    .$compensationButton
                    .$postButton
                    .$editButton
                    .$cancelButton
                    .$deleteButton
                    .'</div>';
            })
            ->rawColumns(['actions'])
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function generateNoRetur()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'RPB-'.$year.'-'.$month.'-';

        $last = ReturPembelianModel::where('nomor_retur', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = $last ? (int) substr($last->nomor_retur, -4) : 0;

        return response()->json($prefix.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT));
    }

    public function postedReceipts()
    {
        $query = PenerimaanBarangModel::with(['purchaseOrder.distributor', 'distributor', 'details'])
            ->where('status', 'posted')
            ->latest('tanggal_penerimaan')
            ->latest('id');

        $this->scopePenerimaanBranch($query);

        $receipts = $query->get()
            ->map(function (PenerimaanBarangModel $penerimaan) {
                $returnableQty = 0;
                $returnableItems = 0;

                foreach ($penerimaan->details as $detail) {
                    $available = $this->returnableQtyForReceiptDetail($detail);
                    $returnableQty += $available;
                    $returnableItems += $available > 0 ? 1 : 0;
                }

                return [
                    'id' => $penerimaan->id,
                    'nomor_penerimaan' => $penerimaan->nomor_penerimaan,
                    'text' => $penerimaan->nomor_penerimaan.' - '.($penerimaan->purchaseOrder->no_po ?? '-').' - '.($penerimaan->distributor->nama ?? 'Supplier tidak diketahui'),
                    'no_po' => $penerimaan->purchaseOrder->no_po ?? '-',
                    'supplier' => $penerimaan->distributor->nama ?? '-',
                    'tanggal_penerimaan' => optional($penerimaan->tanggal_penerimaan)->format('Y-m-d'),
                    'item_count' => $penerimaan->details->count(),
                    'returnable_items' => $returnableItems,
                    'returnable_qty' => $returnableQty,
                ];
            })
            ->filter(fn ($penerimaan) => $penerimaan['returnable_qty'] > 0)
            ->values();

        return response()->json($receipts);
    }

    public function receiptDetail($id)
    {
        return response()->json($this->receiptPayload($id));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        return DB::transaction(function () use ($request) {
            $penerimaan = $this->postedPenerimaan($request->penerimaan_barang_id);
            $computed = $this->buildComputedDetails($request, $penerimaan);

            $retur = ReturPembelianModel::create($this->headerPayload($request, $penerimaan, $computed));

            foreach ($computed['details'] as $detail) {
                $retur->details()->create($detail);
            }

            $creator = Auth::user();
            DB::afterCommit(fn () => $this->transactionNotifications->notifyApprovalRequest('retur_pembelian', $retur, $creator));

            return response()->json([
                'status' => 'success',
                'message' => 'Draft retur pembelian berhasil disimpan.',
            ]);
        });
    }

    public function show($id)
    {
        return response()->json($this->returPayload($id, false));
    }

    public function edit($id)
    {
        $retur = $this->returQueryForBranch(['details'])->findOrFail($id);

        if ($retur->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya retur pembelian berstatus draft yang bisa diedit.',
            ], 422);
        }

        return response()->json($this->returPayload($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules((int) $id));

        return DB::transaction(function () use ($request, $id) {
            $retur = $this->returQueryForBranch(['details'])->findOrFail($id);

            if ($retur->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya retur pembelian berstatus draft yang bisa diedit.',
                ]);
            }

            $penerimaan = $this->postedPenerimaan($request->penerimaan_barang_id);
            $computed = $this->buildComputedDetails($request, $penerimaan, (int) $retur->id);
            $payload = $this->headerPayload($request, $penerimaan, $computed);
            unset($payload['created_by']);

            $retur->update($payload);
            $retur->details()->delete();

            foreach ($computed['details'] as $detail) {
                $retur->details()->create($detail);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Draft retur pembelian berhasil diperbarui.',
            ]);
        });
    }

    public function post($id)
    {
        if (! $this->transactionNotifications->isApprovalRole(Auth::user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki akses approval untuk retur pembelian.',
            ], 403);
        }

        return DB::transaction(function () use ($id) {
            $retur = $this->returQueryForBranch([
                'penerimaanBarang',
                'purchaseOrder',
                'details.obat.satuan',
                'details.purchaseOrderDetail.satuanKonversi.satuan',
                'details.penerimaanBarangDetail',
            ], BranchAccess::approvalBranchIds())->lockForUpdate()->findOrFail($id);

            if ($retur->status !== 'draft') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya draft retur pembelian yang bisa diposting.',
                ], 422);
            }

            $this->validateReturnCanPost($retur);

            $retur->posted_by = Auth::id();
            $retur->posted_at = now();

            foreach ($retur->details as $detail) {
                $this->stockService->recordPurchaseReturn($retur, $detail);
            }

            $retur->status = 'posted';
            $retur->save();
            $actor = Auth::user();
            DB::afterCommit(fn () => $this->transactionNotifications->notifyActionResult('retur_pembelian', $retur, 'posted', $actor));

            return response()->json([
                'status' => 'success',
                'message' => 'Retur pembelian berhasil diposting dan stok batch dikurangi.',
            ]);
        });
    }

    public function cancel($id)
    {
        if (! $this->transactionNotifications->isApprovalRole(Auth::user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki akses approval untuk retur pembelian.',
            ], 403);
        }

        return DB::transaction(function () use ($id) {
            $retur = $this->returQueryForBranch([
                'penerimaanBarang',
                'purchaseOrder',
                'details.obat.satuan',
                'details.purchaseOrderDetail.satuanKonversi.satuan',
            ], BranchAccess::approvalBranchIds())->lockForUpdate()->findOrFail($id);

            if ($retur->status === 'cancelled') {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Retur pembelian sudah dibatalkan.',
                ]);
            }

            if ($retur->activeCompensations()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Retur tidak dapat dibatalkan karena sudah memiliki realisasi ganti rugi supplier. Batalkan realisasi tersebut terlebih dahulu.',
                ], 422);
            }

            $retur->cancelled_by = Auth::id();
            $retur->cancelled_at = now();

            if ($retur->status === 'posted') {
                foreach ($retur->details as $detail) {
                    $this->stockService->reversePurchaseReturn($retur, $detail);
                }
            }

            $retur->status = 'cancelled';
            $retur->save();
            $actor = Auth::user();
            DB::afterCommit(fn () => $this->transactionNotifications->notifyActionResult('retur_pembelian', $retur, 'cancelled', $actor));

            return response()->json([
                'status' => 'success',
                'message' => 'Retur pembelian berhasil dibatalkan.',
            ]);
        });
    }

    public function destroy($id)
    {
        $retur = $this->returQueryForBranch()->findOrFail($id);

        if ($retur->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya draft retur pembelian yang bisa dihapus.',
            ], 422);
        }

        $retur->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Draft retur pembelian berhasil dihapus.',
        ]);
    }

    public function updateCompensationPlan(Request $request, $id)
    {
        $validated = $request->validate([
            'expects_compensation' => ['required', 'boolean'],
            'compensation_due_date' => ['nullable', 'string'],
            'compensation_notes' => ['nullable', 'required_if:expects_compensation,0', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($validated, $id) {
            $retur = $this->returQueryForBranch(['compensations'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($retur->status !== 'posted') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rencana ganti rugi hanya dapat diperbarui setelah retur diposting.',
                ], 422);
            }

            $expectsCompensation = (bool) $validated['expects_compensation'];

            if (! $expectsCompensation && $retur->activeCompensations()->exists()) {
                throw ValidationException::withMessages([
                    'expects_compensation' => 'Batalkan seluruh realisasi sebelum menandai retur tanpa ganti rugi.',
                ]);
            }

            $retur->update([
                'expects_compensation' => $expectsCompensation,
                'compensation_due_date' => $expectsCompensation
                    ? $this->parseOptionalDate($validated['compensation_due_date'] ?? null, 'compensation_due_date', 'batas waktu ganti rugi')
                    : null,
                'compensation_notes' => $validated['compensation_notes'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $expectsCompensation
                    ? 'Rencana ganti rugi supplier berhasil diperbarui.'
                    : 'Retur ditandai tidak memerlukan ganti rugi supplier.',
                'compensation' => $this->compensationSummary($retur->fresh('compensations')),
            ]);
        });
    }

    public function storeCompensation(Request $request, $id)
    {
        $validated = $request->validate([
            'tanggal_realisasi' => ['required', 'string'],
            'jenis' => ['required', 'in:barang_pengganti,potongan_faktur,refund_tunai,transfer_bank,nota_kredit,lainnya'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'nomor_referensi' => ['nullable', 'string', 'max:120'],
            'nomor_faktur' => ['nullable', 'string', 'max:120'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($validated, $id) {
            $retur = $this->returQueryForBranch(['compensations'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($retur->status !== 'posted') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ganti rugi hanya dapat dicatat untuk retur yang sudah diposting.',
                ], 422);
            }

            if (! $retur->expects_compensation) {
                throw ValidationException::withMessages([
                    'expects_compensation' => 'Aktifkan rencana ganti rugi supplier terlebih dahulu.',
                ]);
            }

            $nominal = round((float) $validated['nominal'], 2);
            $outstanding = $retur->compensation_outstanding_value;

            if ($nominal > $outstanding + 0.009) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal realisasi melebihi sisa ganti rugi sebesar Rp '.number_format($outstanding, 0, ',', '.').'.',
                ]);
            }

            $tanggalRealisasi = $this->parseOptionalDate(
                $validated['tanggal_realisasi'],
                'tanggal_realisasi',
                'tanggal realisasi'
            );

            if (Carbon::parse($tanggalRealisasi)->isAfter(now()->endOfDay())) {
                throw ValidationException::withMessages([
                    'tanggal_realisasi' => 'Tanggal realisasi tidak boleh melewati hari ini.',
                ]);
            }

            $retur->compensations()->create([
                'tanggal_realisasi' => $tanggalRealisasi,
                'jenis' => $validated['jenis'],
                'nominal' => $nominal,
                'nomor_referensi' => $validated['nomor_referensi'] ?? null,
                'nomor_faktur' => $validated['nomor_faktur'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Realisasi ganti rugi supplier berhasil dicatat.',
                'compensation' => $this->compensationSummary($retur->fresh('compensations')),
            ]);
        });
    }

    public function cancelCompensation(Request $request, $id, $compensationId)
    {
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($validated, $id, $compensationId) {
            $retur = $this->returQueryForBranch()->lockForUpdate()->findOrFail($id);

            $compensation = ReturPembelianCompensationModel::where('retur_pembelian_id', $retur->id)
                ->lockForUpdate()
                ->findOrFail($compensationId);

            if ($compensation->cancelled_at) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Realisasi ganti rugi tersebut sudah dibatalkan.',
                ]);
            }

            if ($compensation->penerimaan_barang_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Realisasi ini berasal dari potongan faktur penerimaan. Batalkan penerimaan terkait agar nilai faktur dan saldo ganti rugi tetap konsisten.',
                ], 422);
            }

            $compensation->update([
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Realisasi ganti rugi berhasil dibatalkan dan tetap tersimpan dalam riwayat audit.',
                'compensation' => $this->compensationSummary($retur->fresh('compensations')),
            ]);
        });
    }

    private function rules(?int $ignoreId = null): array
    {
        $unique = 'unique:retur_pembelian,nomor_retur';

        if ($ignoreId) {
            $unique .= ','.$ignoreId;
        }

        return [
            'nomor_retur' => ['required', 'string', 'max:60', $unique],
            'penerimaan_barang_id' => ['required', 'integer', 'exists:penerimaan_barang,id'],
            'tanggal_retur' => ['required', 'string'],
            'nomor_referensi_supplier' => ['nullable', 'string', 'max:100'],
            'expects_compensation' => ['sometimes', 'boolean'],
            'compensation_due_date' => ['nullable', 'string'],
            'compensation_notes' => ['nullable', 'required_if:expects_compensation,0', 'string', 'max:2000'],
            'alasan' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'penerimaan_barang_detail_id' => ['required', 'array'],
            'penerimaan_barang_detail_id.*' => ['required', 'integer', 'exists:penerimaan_barang_detail,id'],
            'qty_retur' => ['required', 'array'],
            'qty_retur.*' => ['required', 'numeric', 'min:0'],
            'satuan_retur_id' => ['nullable', 'array'],
            'satuan_retur_id.*' => ['nullable', 'integer', 'exists:satuans,id'],
            'alasan_item' => ['nullable', 'array'],
            'alasan_item.*' => ['nullable', 'string'],
        ];
    }

    private function postedPenerimaan($id): PenerimaanBarangModel
    {
        $query = PenerimaanBarangModel::with([
            'purchaseOrder.branch',
            'purchaseOrder.distributor',
            'distributor',
            'details.obat.satuan',
            'details.obat.konversiSatuan.satuan',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
            'details.stokBatch',
        ]);

        $this->scopePenerimaanBranch($query);

        $penerimaan = $query->findOrFail($id);

        if ($penerimaan->status !== 'posted') {
            throw ValidationException::withMessages([
                'penerimaan_barang_id' => 'Pilih penerimaan yang sudah posted.',
            ]);
        }

        return $penerimaan;
    }

    private function buildComputedDetails(Request $request, PenerimaanBarangModel $penerimaan, ?int $ignoreReturId = null): array
    {
        $detailsById = $penerimaan->details->keyBy('id');
        $branchId = (int) ($penerimaan->purchaseOrder?->branch_id ?: 0);
        $rows = [];
        $summary = [
            'total_barang' => 0,
            'total_qty' => 0,
            'subtotal' => 0,
            'total_diskon' => 0,
            'total_ppn' => 0,
            'grand_total' => 0,
        ];

        foreach ($request->penerimaan_barang_detail_id as $index => $receiptDetailId) {
            $receiptDetail = $detailsById->get((int) $receiptDetailId);

            if (! $receiptDetail) {
                throw ValidationException::withMessages([
                    'penerimaan_barang_detail_id.'.$index => 'Item tidak sesuai dengan penerimaan yang dipilih.',
                ]);
            }

            $qty = (float) ($request->qty_retur[$index] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $returnUnit = $this->resolveReturnUnit(
                $receiptDetail,
                $request->input('satuan_retur_id.'.$index),
                $index
            );
            $availableStock = $this->returnableStockQtyForReceiptDetail($receiptDetail, $ignoreReturId);
            $conversion = (float) $returnUnit['konversi'];
            $qtyStock = $qty * $conversion;

            if ($qtyStock > $availableStock + 0.00001) {
                throw ValidationException::withMessages([
                    'qty_retur.'.$index => 'Qty retur melebihi sisa retur penerimaan ('.$availableStock.' '.$this->stockUnitLabel($receiptDetail).').',
                ]);
            }

            $batch = $this->stockBatchForReceiptDetail($receiptDetail, $index, $branchId);

            if ($qtyStock > (float) $batch->qty + 0.00001) {
                throw ValidationException::withMessages([
                    'qty_retur.'.$index => 'Qty retur melebihi stok batch tersedia ('.$batch->qty.' '.$this->stockUnitLabel($receiptDetail).').',
                ]);
            }

            $purchaseConversion = $this->detailConversionFactor($receiptDetail);
            $hargaStock = (float) ($receiptDetail->harga_beli_stok
                ?: ($purchaseConversion > 0 ? (float) $receiptDetail->harga_beli / $purchaseConversion : $receiptDetail->harga_beli));
            $harga = $hargaStock * $conversion;
            [$diskon1, $diskon2, $diskon3] = TieredDiscount::percentages(
                $receiptDetail->diskon_1,
                $receiptDetail->diskon_2,
                $receiptDetail->diskon_3
            );
            $diskon = TieredDiscount::effectivePercentage($diskon1, $diskon2, $diskon3);
            $ppn = $this->percent($receiptDetail->ppn ?? 0);
            $subtotal = round($qty * $harga, 2);
            $taxBase = TieredDiscount::netAmount($subtotal, $diskon1, $diskon2, $diskon3);
            $nilaiDiskon = round($subtotal - $taxBase, 2);
            $nilaiPpn = round($taxBase * ($ppn / 100), 2);
            $total = round($taxBase + $nilaiPpn, 2);

            $rows[] = [
                'penerimaan_barang_detail_id' => $receiptDetail->id,
                'purchase_order_detail_id' => $receiptDetail->purchase_order_detail_id,
                'obat_id' => (int) $receiptDetail->obat_id,
                'stok_batch_id' => $receiptDetail->stok_batch_id,
                'qty_diterima' => $receiptDetail->qty_diterima,
                'qty_retur' => $qty,
                'qty_retur_stok' => $qtyStock,
                'konversi_satuan' => $conversion,
                'satuan_retur_id' => $returnUnit['satuan_id'],
                'satuan_beli' => $returnUnit['nama'],
                'satuan_stok' => $this->stockUnitLabel($receiptDetail),
                'no_batch' => $receiptDetail->no_batch,
                'expired_date' => $receiptDetail->expired_date,
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
                'alasan_item' => $request->input('alasan_item.'.$index),
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
                'qty_retur' => 'Isi minimal satu qty retur lebih dari 0.',
            ]);
        }

        return ['details' => $rows] + $summary;
    }

    private function headerPayload(Request $request, PenerimaanBarangModel $penerimaan, array $computed): array
    {
        return [
            'nomor_retur' => $request->nomor_retur,
            'penerimaan_barang_id' => $penerimaan->id,
            'purchase_order_id' => $penerimaan->purchase_order_id,
            'distributor_id' => $penerimaan->distributor_id,
            'tanggal_retur' => $this->parseDate($request->tanggal_retur),
            'nomor_referensi_supplier' => $request->nomor_referensi_supplier,
            'total_barang' => $computed['total_barang'],
            'total_qty' => $computed['total_qty'],
            'subtotal' => $computed['subtotal'],
            'total_diskon' => $computed['total_diskon'],
            'total_ppn' => $computed['total_ppn'],
            'grand_total' => $computed['grand_total'],
            'expects_compensation' => $request->has('expects_compensation')
                ? $request->boolean('expects_compensation')
                : true,
            'compensation_due_date' => $request->boolean('expects_compensation', true)
                ? $this->parseOptionalDate($request->compensation_due_date, 'compensation_due_date', 'batas waktu ganti rugi')
                : null,
            'compensation_notes' => $request->compensation_notes,
            'alasan' => $request->alasan,
            'catatan' => $request->catatan,
            'created_by' => Auth::id(),
        ];
    }

    private function returPayload($id, bool $requireReturnableReceipt = true): array
    {
        $retur = $this->returQueryForBranch([
            'penerimaanBarang.purchaseOrder.branch',
            'penerimaanBarang.distributor',
            'purchaseOrder',
            'distributor',
            'details.obat.satuan',
            'details.obat.konversiSatuan.satuan',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
            'createdBy',
            'postedBy',
            'cancelledBy',
            'compensations.createdBy',
            'compensations.cancelledBy',
            'compensations.penerimaanBarang',
        ])->findOrFail($id);

        $compensation = $this->compensationSummary($retur);
        $retur->makeHidden('compensations');

        return [
            'header' => $retur,
            'compensation' => $compensation,
            'penerimaan_payload' => $this->receiptPayload(
                $retur->penerimaan_barang_id,
                $retur->id,
                $requireReturnableReceipt
            ),
        ];
    }

    private function receiptPayload($penerimaanId, ?int $ignoreReturId = null, bool $requireReturnable = true): array
    {
        $query = PenerimaanBarangModel::with([
            'purchaseOrder.branch',
            'purchaseOrder.distributor',
            'distributor',
            'details.obat.satuan',
            'details.obat.konversiSatuan.satuan',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
            'details.stokBatch',
        ]);

        $this->scopePenerimaanBranch($query);

        $penerimaan = $query->findOrFail($penerimaanId);

        if ($penerimaan->status !== 'posted') {
            throw ValidationException::withMessages([
                'penerimaan_barang_id' => 'Penerimaan belum diposting.',
            ]);
        }

        $details = $penerimaan->details
            ->map(function (PenerimaanBarangDetailModel $detail) use ($ignoreReturId) {
                $conversion = $this->detailConversionFactor($detail);
                $returnedQtyStock = $this->returnedStockQtyForReceiptDetail($detail->id, $ignoreReturId);
                $receivedQtyStock = $this->receivedStockQty($detail);
                $returnableQtyStock = max(0, $receivedQtyStock - $returnedQtyStock);
                $returnedQty = $conversion > 0 ? $returnedQtyStock / $conversion : 0;
                $returnableQty = $conversion > 0 ? $returnableQtyStock / $conversion : 0;

                return [
                    'id' => $detail->id,
                    'purchase_order_detail_id' => $detail->purchase_order_detail_id,
                    'obat_id' => $detail->obat_id,
                    'stok_batch_id' => $detail->stok_batch_id,
                    'kode_obat' => $detail->obat->kode_obat ?? '-',
                    'nama_obat' => $detail->obat->nama_obat ?? '-',
                    'satuan' => $this->purchaseUnitLabel($detail),
                    'purchase_satuan_id' => $detail->purchaseOrderDetail?->satuanKonversi?->satuan_id
                        ?: $detail->obat?->satuan_id,
                    'satuan_stok' => $this->stockUnitLabel($detail),
                    'konversi' => $conversion,
                    'units' => $this->returnUnitsForReceiptDetail($detail),
                    'qty_diterima' => (float) $detail->qty_diterima,
                    'qty_diterima_stok' => (float) ($detail->qty_diterima_stok ?: ((float) $detail->qty_diterima * $conversion)),
                    'returned_qty' => $returnedQty,
                    'returnable_qty' => $returnableQty,
                    'returned_qty_stok' => $returnedQtyStock,
                    'returnable_qty_stok' => $returnableQtyStock,
                    'no_batch' => $detail->no_batch,
                    'expired_date' => optional($detail->expired_date)->format('Y-m-d'),
                    'harga_beli' => (float) $detail->harga_beli,
                    'harga_beli_stok' => (float) $detail->harga_beli_stok,
                    'diskon_1' => (float) ($detail->diskon_1 ?? 0),
                    'diskon_2' => (float) ($detail->diskon_2 ?? 0),
                    'diskon_3' => (float) ($detail->diskon_3 ?? 0),
                    'diskon' => (float) ($detail->diskon ?? 0),
                    'ppn' => (float) ($detail->ppn ?? 0),
                    'batch_stock' => (float) ($detail->stokBatch->qty ?? 0),
                ];
            })
            ->values();

        if ($requireReturnable && $details->sum('returnable_qty') <= 0) {
            throw ValidationException::withMessages([
                'penerimaan_barang_id' => 'Semua item penerimaan ini sudah diretur.',
            ]);
        }

        return [
            'id' => $penerimaan->id,
            'nomor_penerimaan' => $penerimaan->nomor_penerimaan,
            'no_po' => $penerimaan->purchaseOrder->no_po ?? '-',
            'supplier' => $penerimaan->distributor->nama ?? '-',
            'distributor_id' => $penerimaan->distributor_id,
            'branch' => $penerimaan->purchaseOrder->branch->name ?? '-',
            'tanggal_penerimaan' => optional($penerimaan->tanggal_penerimaan)->format('Y-m-d'),
            'nomor_faktur' => $penerimaan->nomor_faktur,
            'grand_total' => $penerimaan->grand_total,
            'details' => $details,
        ];
    }

    private function validateReturnCanPost(ReturPembelianModel $retur): void
    {
        $branchId = (int) ($retur->purchaseOrder?->branch_id ?: 0);

        foreach ($retur->details as $index => $detail) {
            $receiptDetail = $detail->penerimaanBarangDetail;

            if (! $receiptDetail) {
                throw ValidationException::withMessages([
                    'detail.'.$index => 'Detail penerimaan asal tidak ditemukan.',
                ]);
            }

            $availableStock = $this->returnableStockQtyForReceiptDetail($receiptDetail, $retur->id);
            $qtyStock = $this->returnDetailStockQuantity($detail);

            if ($qtyStock > $availableStock + 0.00001) {
                throw ValidationException::withMessages([
                    'qty_retur.'.$index => 'Qty retur melebihi sisa retur penerimaan ('.$availableStock.' '.$this->stockUnitLabel($receiptDetail).').',
                ]);
            }

            $batch = StokBatchModel::where('id', $detail->stok_batch_id)
                ->where('obat_id', $detail->obat_id)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (! $batch) {
                throw ValidationException::withMessages([
                    'stok_batch_id.'.$index => 'Batch stok retur tidak ditemukan.',
                ]);
            }

            if ($qtyStock > (float) $batch->qty + 0.00001) {
                throw ValidationException::withMessages([
                    'qty_retur.'.$index => 'Qty retur melebihi stok batch tersedia ('.$batch->qty.' '.$detail->satuan_stok.').',
                ]);
            }
        }
    }

    private function stockBatchForReceiptDetail(PenerimaanBarangDetailModel $detail, int $index, int $branchId): StokBatchModel
    {
        if (! $detail->stok_batch_id) {
            throw ValidationException::withMessages([
                'penerimaan_barang_detail_id.'.$index => 'Detail penerimaan belum memiliki batch stok.',
            ]);
        }

        $batch = StokBatchModel::where('id', $detail->stok_batch_id)
            ->where('obat_id', $detail->obat_id)
            ->where('branch_id', $branchId)
            ->first();

        if (! $batch) {
            throw ValidationException::withMessages([
                'penerimaan_barang_detail_id.'.$index => 'Batch stok detail penerimaan tidak ditemukan.',
            ]);
        }

        return $batch;
    }

    private function returnableQtyForReceiptDetail(PenerimaanBarangDetailModel $detail, ?int $ignoreReturId = null): float
    {
        $conversion = $this->detailConversionFactor($detail);

        return $conversion > 0
            ? $this->returnableStockQtyForReceiptDetail($detail, $ignoreReturId) / $conversion
            : 0;
    }

    private function returnableStockQtyForReceiptDetail(PenerimaanBarangDetailModel $detail, ?int $ignoreReturId = null): float
    {
        return max(0, $this->receivedStockQty($detail) - $this->returnedStockQtyForReceiptDetail($detail->id, $ignoreReturId));
    }

    private function returnedStockQtyForReceiptDetail($receiptDetailId, ?int $ignoreReturId = null): float
    {
        return (float) ReturPembelianDetailModel::where('penerimaan_barang_detail_id', $receiptDetailId)
            ->whereHas('returPembelian', function ($query) use ($ignoreReturId) {
                $query->where('status', '!=', 'cancelled');

                if ($ignoreReturId) {
                    $query->where('id', '!=', $ignoreReturId);
                }
            })
            ->sum('qty_retur_stok');
    }

    private function receivedStockQty(PenerimaanBarangDetailModel $detail): float
    {
        $storedQty = (float) ($detail->qty_diterima_stok ?? 0);

        if ($storedQty > 0) {
            return $storedQty;
        }

        return (float) $detail->qty_diterima * $this->detailConversionFactor($detail);
    }

    private function returnUnitsForReceiptDetail(PenerimaanBarangDetailModel $detail): array
    {
        $units = collect();
        $stockUnit = $detail->obat?->satuan;

        if ($detail->obat?->satuan_id && $stockUnit) {
            $units->put((int) $detail->obat->satuan_id, [
                'satuan_id' => (int) $detail->obat->satuan_id,
                'nama' => $stockUnit->nama,
                'konversi' => 1.0,
            ]);
        }

        foreach ($detail->obat?->konversiSatuan ?? [] as $conversion) {
            if (! $conversion->satuan_id || ! $conversion->satuan || (float) $conversion->konversi <= 0) {
                continue;
            }

            $units->put((int) $conversion->satuan_id, [
                'satuan_id' => (int) $conversion->satuan_id,
                'nama' => $conversion->satuan->nama,
                'konversi' => (float) $conversion->konversi,
            ]);
        }

        $purchaseConversion = $detail->purchaseOrderDetail?->satuanKonversi;

        if ($purchaseConversion?->satuan_id && $purchaseConversion->satuan && (float) $purchaseConversion->konversi > 0) {
            $units->put((int) $purchaseConversion->satuan_id, [
                'satuan_id' => (int) $purchaseConversion->satuan_id,
                'nama' => $purchaseConversion->satuan->nama,
                'konversi' => (float) $purchaseConversion->konversi,
            ]);
        }

        $purchaseUnitId = (int) ($purchaseConversion?->satuan_id ?: $detail->obat?->satuan_id);

        return $units
            ->map(function (array $unit) use ($purchaseUnitId) {
                $unit['is_purchase'] = (int) $unit['satuan_id'] === $purchaseUnitId;

                return $unit;
            })
            ->sortBy(fn (array $unit) => ($unit['is_purchase'] ? '0-' : '1-').str_pad((string) $unit['konversi'], 20, '0', STR_PAD_LEFT))
            ->values()
            ->all();
    }

    private function resolveReturnUnit(PenerimaanBarangDetailModel $detail, $satuanId, int $index): array
    {
        $units = collect($this->returnUnitsForReceiptDetail($detail));

        if ($units->isEmpty()) {
            throw ValidationException::withMessages([
                'satuan_retur_id.'.$index => 'Satuan retur untuk obat ini belum tersedia.',
            ]);
        }

        if ($satuanId !== null && $satuanId !== '') {
            $selected = $units->first(fn (array $unit) => (int) $unit['satuan_id'] === (int) $satuanId);

            if (! $selected) {
                throw ValidationException::withMessages([
                    'satuan_retur_id.'.$index => 'Satuan retur tidak terdaftar untuk obat ini.',
                ]);
            }

            return $selected;
        }

        return $units->firstWhere('is_purchase', true) ?: $units->first();
    }

    private function returQueryForBranch(array $with = [], ?array $branchIds = null)
    {
        $query = ReturPembelianModel::with($with);

        $this->scopeReturBranch($query, $branchIds);

        return $query;
    }

    private function scopeReturBranch($query, ?array $branchIds = null): void
    {
        $branchIds = $branchIds ?? BranchAccess::userBranchIds();

        $query->whereHas('purchaseOrder', function ($purchaseOrderQuery) use ($branchIds) {
            $this->scopePurchaseOrderBranch($purchaseOrderQuery, $branchIds);
        });
    }

    private function scopePenerimaanBranch($query): void
    {
        $branchIds = BranchAccess::userBranchIds();

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

    private function parseDate($date): string
    {
        $date = trim((string) $date);

        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'tanggal_retur' => 'Format tanggal retur tidak valid.',
            ]);
        }
    }

    private function parseOptionalDate($date, string $field, string $label): ?string
    {
        $date = trim((string) $date);

        if ($date === '') {
            return null;
        }

        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Format '.$label.' tidak valid.',
            ]);
        }
    }

    private function compensationSummary(ReturPembelianModel $retur): array
    {
        if (! $retur->relationLoaded('compensations')) {
            $retur->load(['compensations.createdBy', 'compensations.cancelledBy']);
        }

        return [
            'expects_compensation' => (bool) $retur->expects_compensation,
            'due_date' => optional($retur->compensation_due_date)->format('Y-m-d'),
            'notes' => $retur->compensation_notes,
            'expected_value' => $retur->compensation_expected_value,
            'received_value' => $retur->compensation_received_value,
            'outstanding_value' => $retur->compensation_outstanding_value,
            'status' => $retur->compensation_status,
            'entries' => $retur->compensations
                ->sortByDesc(fn (ReturPembelianCompensationModel $entry) => optional($entry->tanggal_realisasi)->format('Y-m-d').'-'.str_pad((string) $entry->id, 20, '0', STR_PAD_LEFT)
                )
                ->map(fn (ReturPembelianCompensationModel $entry) => [
                    'id' => $entry->id,
                    'tanggal_realisasi' => optional($entry->tanggal_realisasi)->format('Y-m-d'),
                    'jenis' => $entry->jenis,
                    'nominal' => (float) $entry->nominal,
                    'nomor_referensi' => $entry->nomor_referensi,
                    'nomor_faktur' => $entry->nomor_faktur,
                    'keterangan' => $entry->keterangan,
                    'created_by' => ['name' => $entry->createdBy?->name ?? '-'],
                    'penerimaan_barang' => $entry->penerimaanBarang
                        ? [
                            'id' => $entry->penerimaanBarang->id,
                            'nomor_penerimaan' => $entry->penerimaanBarang->nomor_penerimaan,
                        ]
                        : null,
                    'cancelled_at' => optional($entry->cancelled_at)->toISOString(),
                    'cancelled_by' => $entry->cancelled_at
                        ? ['name' => $entry->cancelledBy?->name ?? '-']
                        : null,
                    'cancellation_reason' => $entry->cancellation_reason,
                ])
                ->values()
                ->all(),
        ];
    }

    private function detailConversionFactor(PenerimaanBarangDetailModel|ReturPembelianDetailModel $detail): float
    {
        $storedConversion = (float) ($detail->konversi_satuan ?? 0);

        if ($storedConversion > 0) {
            return max(1, $storedConversion);
        }

        return max(1, (float) ($detail->purchaseOrderDetail?->satuanKonversi?->konversi ?? 1));
    }

    private function returnDetailStockQuantity(ReturPembelianDetailModel $detail): float
    {
        $storedQty = (float) ($detail->qty_retur_stok ?? 0);

        if ($storedQty > 0) {
            return $storedQty;
        }

        return (float) $detail->qty_retur * $this->detailConversionFactor($detail);
    }

    private function purchaseUnitLabel(PenerimaanBarangDetailModel|ReturPembelianDetailModel $detail): string
    {
        return $detail->satuan_beli
            ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama ?? ($detail->obat->satuan->nama ?? 'satuan'));
    }

    private function stockUnitLabel(PenerimaanBarangDetailModel|ReturPembelianDetailModel $detail): string
    {
        return $detail->satuan_stok ?: ($detail->obat->satuan->nama ?? 'PCS');
    }

    private function percent($value): float
    {
        return round(min(100, max(0, (float) ($value ?: 0))), 2);
    }
}
