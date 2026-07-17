<?php

namespace App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Penerimaan;

use App\Http\Controllers\Controller;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Services\Menu\Stok\StockService;
use App\Services\Notifikasi\TransactionNotificationService;
use App\Services\Settings\Margins\MarginsService;
use App\Support\BranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanController extends Controller
{
    private const RECEIVABLE_PO_STATUSES = ['approved', 'diterima_sebagian'];

    public function __construct(
        private readonly StockService $stockService,
        private readonly TransactionNotificationService $transactionNotifications,
        private readonly MarginsService $marginsService
    ) {}

    public function penerimaan()
    {
        return view('medcare.menu.pembelianPenerimaan.penerimaan.penerimaan');
    }

    public function table(Request $request)
    {
        $query = PenerimaanBarangModel::with(['purchaseOrder', 'distributor', 'createdBy'])
            ->latest('tanggal_penerimaan')
            ->latest('id');

        $this->scopePenerimaanBranch($query);

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
        $canApprove = $this->transactionNotifications->isApprovalRole(Auth::user());

        return DataTables::of($penerimaan)
            ->addIndexColumn()
            ->addColumn('no_po', fn ($row) => $row->purchaseOrder->no_po ?? '-')
            ->addColumn('supplier', fn ($row) => $row->distributor->nama ?? '-')
            ->addColumn('tanggal', fn ($row) => optional($row->tanggal_penerimaan)->format('Y-m-d'))
            ->addColumn('user', fn ($row) => $row->createdBy->name ?? '-')
            ->addColumn('actions', function ($row) use ($canApprove) {
                $detailButton = '<button class="btn btn-sm btn-info" onclick="lihatPenerimaan('.$row->id.')"><i class="mdi mdi-eye"></i></button>';
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

                return $detailButton.' '.$editButton.' '.$postButton.' '.$cancelButton.' '.$deleteButton;
            })
            ->rawColumns(['actions'])
            ->with(['summary' => $summary])
            ->make(true);
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
        $query = PembelianModel::with(['distributor', 'details'])
            ->whereIn('status', self::RECEIVABLE_PO_STATUSES)
            ->latest('tanggal_po');

        $this->scopePurchaseOrderBranch($query);

        $orders = $query->get()
            ->map(function ($po) {
                $totalOutstanding = 0;
                $outstandingItems = 0;

                foreach ($po->details as $detail) {
                    $outstanding = max(0, (float) $detail->qty - $this->receivedQtyForPoDetail($detail->id));
                    $totalOutstanding += $outstanding;
                    $outstandingItems += $outstanding > 0 ? 1 : 0;
                }

                return [
                    'id' => $po->id,
                    'no_po' => $po->no_po,
                    'text' => $po->no_po.' - '.($po->distributor->nama ?? 'Supplier tidak diketahui'),
                    'supplier' => $po->distributor->nama ?? '-',
                    'tanggal_po' => $po->tanggal_po,
                    'item_count' => $po->details->count(),
                    'outstanding_items' => $outstandingItems,
                    'outstanding_qty' => $totalOutstanding,
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

            $penerimaan = PenerimaanBarangModel::create($this->headerPayload($request, $po, $computed));

            foreach ($computed['details'] as $detail) {
                $penerimaan->details()->create($detail);
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

    public function hargaJualPreview($id)
    {
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

        return response()->json($this->sellingPricePayload($penerimaan));
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules($id));

        return DB::transaction(function () use ($request, $id) {
            $penerimaan = $this->penerimaanQueryForBranch(['details'])->findOrFail($id);

            if ($penerimaan->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya penerimaan berstatus draft yang bisa diedit.',
                ]);
            }

            $po = $this->approvedPo($request->purchase_order_id);
            $computed = $this->buildComputedDetails($request, $po, $penerimaan->id);

            $payload = $this->headerPayload($request, $po, $computed);
            unset($payload['created_by']);

            $penerimaan->update($payload);
            $penerimaan->details()->delete();

            foreach ($computed['details'] as $detail) {
                $penerimaan->details()->create($detail);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Draft penerimaan barang berhasil diperbarui.',
            ]);
        });
    }

    public function post($id)
    {
        if (! $this->transactionNotifications->isApprovalRole(Auth::user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya admin/apoteker yang dapat memposting penerimaan.',
            ], 403);
        }

        return DB::transaction(function () use ($id) {
            $penerimaan = $this->penerimaanQueryForBranch([
                'purchaseOrder',
                'details.obat.satuan',
                'details.obat.golongan',
                'details.obat.mainGolongan',
                'details.obat.subGolongan',
                'details.purchaseOrderDetail.satuanKonversi.satuan',
            ])->lockForUpdate()->findOrFail($id);

            if ($penerimaan->status !== 'draft') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya draft penerimaan yang bisa diposting.',
                ], 422);
            }

            $sellingPricesByDetailId = $this->sellingPriceRowsByDetailId($penerimaan);

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
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            $this->syncPurchaseOrderReceivingStatus($penerimaan->purchase_order_id);
            $actor = Auth::user();
            DB::afterCommit(fn () => $this->transactionNotifications->notifyActionResult('penerimaan', $penerimaan, 'posted', $actor));

            return response()->json([
                'status' => 'success',
                'message' => 'Penerimaan berhasil diposting, stok obat diperbarui, dan harga jual batch tersimpan.',
                'selling_prices' => $sellingPrices,
            ]);
        });
    }

    public function cancel($id)
    {
        if (! $this->transactionNotifications->isApprovalRole(Auth::user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya admin/apoteker yang dapat membatalkan penerimaan.',
            ], 403);
        }

        return DB::transaction(function () use ($id) {
            $penerimaan = $this->penerimaanQueryForBranch(['details.obat'])->lockForUpdate()->findOrFail($id);

            if ($penerimaan->status === 'cancelled') {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Penerimaan sudah dibatalkan.',
                ]);
            }

            if ($penerimaan->status === 'posted') {
                foreach ($penerimaan->details as $detail) {
                    $this->stockService->reverseReceipt($penerimaan, $detail);
                }
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
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'pajak' => ['nullable', 'numeric', 'min:0'],
            'biaya_lain' => ['nullable', 'numeric', 'min:0'],
            'total_faktur' => ['nullable', 'numeric', 'min:0'],
            'status_pembayaran' => ['nullable', 'in:belum_dibayar,sebagian,lunas'],
            'jumlah_dibayar' => ['nullable', 'numeric', 'min:0'],
            'sisa_hutang' => ['nullable', 'numeric', 'min:0'],
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
            'diskon' => ['required', 'array'],
            'diskon.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
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

        $po = $query->findOrFail($id);

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

            $outstanding = max(0, (float) $poDetail->qty - $this->receivedQtyForPoDetail($poDetail->id, $ignorePenerimaanId));

            if ($qty > $outstanding) {
                throw ValidationException::withMessages([
                    'qty_diterima.'.$index => 'Qty diterima melebihi sisa PO ('.$outstanding.').',
                ]);
            }

            $harga = (float) ($request->harga_beli[$index] ?? 0);
            $conversion = $this->conversionFactor($poDetail);
            $qtyStock = $qty * $conversion;
            $hargaStock = $conversion > 0 ? $harga / $conversion : $harga;
            $diskon = $this->discountPercent($request->diskon[$index] ?? 0);
            $ppn = (float) ($request->ppn[$index] ?? 0);
            $subtotal = $qty * $harga;
            $nilaiDiskon = $subtotal * ($diskon / 100);
            $taxBase = max(0, $subtotal - $nilaiDiskon);
            $nilaiPpn = $taxBase * ($ppn / 100);
            $total = $taxBase + $nilaiPpn;

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

    private function headerPayload(Request $request, PembelianModel $po, array $computed): array
    {
        $subtotal = (float) $computed['subtotal'];
        $diskon = (float) $computed['total_diskon'];
        $pajak = (float) $computed['total_ppn'];
        $biayaLain = $this->moneyValue($request->biaya_lain);
        $totalFaktur = max(0, $subtotal - $diskon + $pajak + $biayaLain);
        $jumlahDibayar = min($this->moneyValue($request->jumlah_dibayar), $totalFaktur);
        $sisaHutang = max(0, $totalFaktur - $jumlahDibayar);

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
            'grand_total' => $totalFaktur,
            'diskon' => $diskon,
            'pajak' => $pajak,
            'biaya_lain' => $biayaLain,
            'total_faktur' => $totalFaktur,
            'status_pembayaran' => $this->paymentStatus($totalFaktur, $jumlahDibayar),
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
            $outstanding = max(0, (float) $detail->qty - $received);

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
                'harga_estimasi_stok' => $this->conversionFactor($detail) > 0
                    ? (float) $detail->harga_estimasi / $this->conversionFactor($detail)
                    : (float) $detail->harga_estimasi,
                'batch_options' => $batchOptionsByObat[(int) $detail->obat_id] ?? [],
            ];
        })->values();

        return [
            'id' => $po->id,
            'no_po' => $po->no_po,
            'supplier' => $po->distributor->nama ?? '-',
            'distributor_id' => $po->distributor_id,
            'branch' => $po->branch->name ?? '-',
            'tanggal_po' => $po->tanggal_po,
            'total_estimasi' => $po->total_estimasi,
            'catatan' => $po->catatan,
            'details' => $details,
        ];
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

        return [
            'id' => $batch->id,
            'text' => $batch->no_batch
                .' | ED '.($expiredDate ?: '-')
                .' | Diskon '.number_format((float) ($batch->diskon ?? 0), 2, ',', '.').'%'
                .' | PPN '.number_format((float) ($batch->ppn ?? 0), 2, ',', '.').'%'
                .' | Stok '.number_format((float) $batch->qty, 2, ',', '.'),
            'no_batch' => $batch->no_batch,
            'expired_date' => $expiredDate,
            'qty' => (float) $batch->qty,
            'harga_beli' => (float) $batch->harga_beli,
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

    private function paymentStatus(float $totalFaktur, float $jumlahDibayar): string
    {
        if ($totalFaktur <= 0 || $jumlahDibayar <= 0) {
            return 'belum_dibayar';
        }

        return $jumlahDibayar >= $totalFaktur ? 'lunas' : 'sebagian';
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

    private function sellingPricePayload(PenerimaanBarangModel $penerimaan): array
    {
        $details = $penerimaan->details
            ->map(fn (PenerimaanBarangDetailModel $detail) => $this->sellingPriceRow($detail))
            ->values();

        return [
            'header' => [
                'id' => $penerimaan->id,
                'nomor_penerimaan' => $penerimaan->nomor_penerimaan,
                'no_po' => $penerimaan->purchaseOrder->no_po ?? '-',
                'supplier' => $penerimaan->distributor->nama ?? '-',
                'tanggal_penerimaan' => optional($penerimaan->tanggal_penerimaan)->format('Y-m-d'),
            ],
            'details' => $details,
            'summary' => [
                'item_count' => $details->count(),
                'missing_margin_count' => $details->where('has_margin', false)->count(),
            ],
        ];
    }

    private function sellingPriceRowsByDetailId(PenerimaanBarangModel $penerimaan): array
    {
        return $penerimaan->details
            ->mapWithKeys(fn (PenerimaanBarangDetailModel $detail) => [
                $detail->id => $this->sellingPriceRow($detail),
            ])
            ->all();
    }

    private function sellingPriceRow(PenerimaanBarangDetailModel $detail): array
    {
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
        $totalHargaBeli = $this->detailTotalPurchasePriceIncludingTax($detail);
        $ppn = (float) ($detail->ppn ?? 0);
        $diskon = (float) ($detail->diskon ?? 0);
        $margin = $this->marginsService->activeMarginForObat($obat);
        $faktorJual = $margin ? (float) $margin->faktor_jual : 1.0;
        $marginReference = $this->marginsService->marginReferenceLabelForObat($obat, $margin);

        if ($qtySatuanTerkecil <= 0) {
            throw ValidationException::withMessages([
                'qty_diterima' => 'Qty satuan terkecil tidak valid untuk menghitung harga jual.',
            ]);
        }

        $nilaiDiskon = $this->detailDiscountValue($detail, $totalHargaBeli, $diskon);
        $totalHargaJual = max(0, $totalHargaBeli * $faktorJual);
        $hargaJual = round($totalHargaJual / $qtySatuanTerkecil, 2);
        $hargaBeliTerkecil = $totalHargaBeli / $qtySatuanTerkecil;
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
            'total_harga_beli' => round($totalHargaBeli, 2),
            'harga_beli_satuan_terkecil' => round($hargaBeliTerkecil, 2),
            'ppn' => $ppn,
            'faktor_jual' => $faktorJual,
            'has_margin' => (bool) $margin,
            'margin_tingkat' => $margin?->tingkat,
            'margin_reference' => $marginReference,
            'diskon' => $diskon,
            'nilai_diskon_beli' => round($nilaiDiskon, 2),
            'nilai_diskon_jual' => round($nilaiDiskon, 2),
            'total_harga_beli_include_ppn' => round($totalHargaBeli, 2),
            'total_harga_jual' => round($totalHargaJual, 2),
            'harga_jual' => $hargaJual,
        ];
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

    private function detailDiscountValue(PenerimaanBarangDetailModel $detail, float $totalHargaBeli, float $diskon): float
    {
        $storedDiscount = (float) ($detail->nilai_diskon ?? 0);

        if ($storedDiscount > 0) {
            return $storedDiscount;
        }

        $subtotal = (float) ($detail->subtotal ?? ((float) $detail->qty_diterima * (float) $detail->harga_beli));

        return $subtotal > 0 ? $subtotal * ($diskon / 100) : $totalHargaBeli * ($diskon / 100);
    }

    private function penerimaanQueryForBranch(array $with = [])
    {
        $query = PenerimaanBarangModel::with($with);

        $this->scopePenerimaanBranch($query);

        return $query;
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
