<?php

namespace App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Penerimaan;

use App\Http\Controllers\Controller;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Services\Menu\Stok\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanController extends Controller
{
    private const RECEIVABLE_PO_STATUSES = ['approved', 'diterima_sebagian'];

    public function __construct(private readonly StockService $stockService)
    {
    }

    public function penerimaan()
    {
        return view('medcare.menu.pembelianPenerimaan.penerimaan.penerimaan');
    }

    public function table(Request $request)
    {
        $query = PenerimaanBarangModel::with(['purchaseOrder', 'distributor', 'createdBy'])
            ->latest('tanggal_penerimaan')
            ->latest('id');

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

        return DataTables::of($penerimaan)
            ->addIndexColumn()
            ->addColumn('no_po', fn ($row) => $row->purchaseOrder->no_po ?? '-')
            ->addColumn('supplier', fn ($row) => $row->distributor->nama ?? '-')
            ->addColumn('tanggal', fn ($row) => optional($row->tanggal_penerimaan)->format('Y-m-d'))
            ->addColumn('user', fn ($row) => $row->createdBy->name ?? '-')
            ->addColumn('actions', function ($row) {
                $detailButton = '<button class="btn btn-sm btn-info" onclick="lihatPenerimaan(' . $row->id . ')"><i class="mdi mdi-eye"></i></button>';
                $editButton = $row->status === 'draft'
                    ? '<button class="btn btn-sm btn-success" onclick="editPenerimaan(' . $row->id . ')"><i class="mdi mdi-pencil"></i></button>'
                    : '';
                $postButton = $row->status === 'draft'
                    ? '<button class="btn btn-sm btn-primary" onclick="postPenerimaan(' . $row->id . ')"><i class="mdi mdi-send-check-outline"></i></button>'
                    : '';
                $cancelButton = $row->status !== 'cancelled'
                    ? '<button class="btn btn-sm btn-warning" onclick="cancelPenerimaan(' . $row->id . ')"><i class="mdi mdi-cancel"></i></button>'
                    : '';
                $deleteButton = $row->status === 'draft'
                    ? '<button class="btn btn-sm btn-danger" onclick="deletePenerimaan(' . $row->id . ')"><i class="mdi mdi-delete"></i></button>'
                    : '';

                return $detailButton . ' ' . $editButton . ' ' . $postButton . ' ' . $cancelButton . ' ' . $deleteButton;
            })
            ->rawColumns(['actions'])
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function generateNoPenerimaan()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'PB-' . $year . '-' . $month . '-';

        $last = PenerimaanBarangModel::where('nomor_penerimaan', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = $last ? (int) substr($last->nomor_penerimaan, -4) : 0;

        return response()->json($prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT));
    }

    public function approvedPurchaseOrders()
    {
        $orders = PembelianModel::with(['distributor', 'details'])
            ->whereIn('status', self::RECEIVABLE_PO_STATUSES)
            ->latest('tanggal_po')
            ->get()
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
                    'text' => $po->no_po . ' - ' . ($po->distributor->nama ?? 'Supplier tidak diketahui'),
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
        $penerimaan = PenerimaanBarangModel::findOrFail($id);

        if ($penerimaan->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya penerimaan berstatus draft yang bisa diedit.',
            ], 422);
        }

        return response()->json($this->penerimaanPayload($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules($id));

        return DB::transaction(function () use ($request, $id) {
            $penerimaan = PenerimaanBarangModel::with('details')->findOrFail($id);

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
        return DB::transaction(function () use ($id) {
            $penerimaan = PenerimaanBarangModel::with([
                'purchaseOrder',
                'details.obat.satuan',
                'details.purchaseOrderDetail.satuanKonversi.satuan',
            ])->lockForUpdate()->findOrFail($id);

            if ($penerimaan->status !== 'draft') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya draft penerimaan yang bisa diposting.',
                ], 422);
            }

            foreach ($penerimaan->details as $detail) {
                $this->stockService->recordReceipt($penerimaan, $detail);
            }

            $penerimaan->update([
                'status' => 'posted',
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            $this->syncPurchaseOrderReceivingStatus($penerimaan->purchase_order_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Penerimaan berhasil diposting dan stok obat diperbarui.',
            ]);
        });
    }

    public function cancel($id)
    {
        return DB::transaction(function () use ($id) {
            $penerimaan = PenerimaanBarangModel::with('details.obat')->lockForUpdate()->findOrFail($id);

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

            return response()->json([
                'status' => 'success',
                'message' => 'Penerimaan berhasil dibatalkan.',
            ]);
        });
    }

    public function destroy($id)
    {
        $penerimaan = PenerimaanBarangModel::findOrFail($id);

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
            $unique .= ',' . $ignoreId;
        }

        return [
            'nomor_penerimaan' => ['required', 'string', 'max:60', $unique],
            'purchase_order_id' => ['required', 'integer', 'exists:purchase_orders,id'],
            'nomor_faktur' => ['required', 'string', 'max:100'],
            'nomor_surat_jalan' => ['nullable', 'string', 'max:100'],
            'tanggal_penerimaan' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
            'purchase_order_detail_id' => ['required', 'array'],
            'purchase_order_detail_id.*' => ['required', 'integer', 'exists:purchase_order_details,id'],
            'obat_id' => ['required', 'array'],
            'obat_id.*' => ['required', 'integer', 'exists:master_obats,id'],
            'qty_diterima' => ['required', 'array'],
            'qty_diterima.*' => ['required', 'numeric', 'min:0'],
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
        $po = PembelianModel::with([
            'distributor',
            'details.obat.satuan',
            'details.satuanKonversi.satuan',
        ])->findOrFail($id);

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
                    'purchase_order_detail_id.' . $index => 'Item tidak sesuai dengan PO yang dipilih.',
                ]);
            }

            $qty = (float) ($request->qty_diterima[$index] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $batch = trim((string) ($request->no_batch[$index] ?? ''));
            $expired = trim((string) ($request->expired_date[$index] ?? ''));

            if ($batch === '') {
                throw ValidationException::withMessages(['no_batch.' . $index => 'Nomor batch wajib diisi.']);
            }

            if ($expired === '') {
                throw ValidationException::withMessages(['expired_date.' . $index => 'Expired date wajib diisi.']);
            }

            $outstanding = max(0, (float) $poDetail->qty - $this->receivedQtyForPoDetail($poDetail->id, $ignorePenerimaanId));

            if ($qty > $outstanding) {
                throw ValidationException::withMessages([
                    'qty_diterima.' . $index => 'Qty diterima melebihi sisa PO (' . $outstanding . ').',
                ]);
            }

            $harga = (float) ($request->harga_beli[$index] ?? 0);
            $conversion = $this->conversionFactor($poDetail);
            $qtyStock = $qty * $conversion;
            $hargaStock = $conversion > 0 ? $harga / $conversion : $harga;
            $diskon = (float) ($request->diskon[$index] ?? 0);
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
                'no_batch' => $batch,
                'expired_date' => $this->parseDate($expired),
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
        return [
            'nomor_penerimaan' => $request->nomor_penerimaan,
            'purchase_order_id' => $po->id,
            'distributor_id' => $po->distributor_id,
            'nomor_faktur' => $request->nomor_faktur,
            'nomor_surat_jalan' => $request->nomor_surat_jalan,
            'tanggal_penerimaan' => $this->parseDate($request->tanggal_penerimaan),
            'total_barang' => $computed['total_barang'],
            'total_qty' => $computed['total_qty'],
            'subtotal' => $computed['subtotal'],
            'total_diskon' => $computed['total_diskon'],
            'total_ppn' => $computed['total_ppn'],
            'grand_total' => $computed['grand_total'],
            'catatan' => $request->catatan,
            'created_by' => Auth::id(),
        ];
    }

    private function penerimaanPayload($id, bool $requireReceivablePo = true): array
    {
        $penerimaan = PenerimaanBarangModel::with([
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
    ): array
    {
        $po = PembelianModel::with([
            'distributor',
            'branch',
            'details.obat',
            'details.obat.satuan',
            'details.satuanKonversi.satuan',
        ])->findOrFail($poId);

        if ($requireReceivableStatus && ! in_array($po->status, self::RECEIVABLE_PO_STATUSES, true)) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'PO belum disetujui atau sudah selesai.',
            ]);
        }

        $details = $po->details->map(function ($detail) use ($ignorePenerimaanId) {
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
                'tanggal_penerimaan' => 'Format tanggal tidak valid.',
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
