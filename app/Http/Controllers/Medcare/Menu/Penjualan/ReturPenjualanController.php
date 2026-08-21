<?php

namespace App\Http\Controllers\Medcare\Menu\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Services\Menu\Penjualan\ReturPenjualanService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ReturPenjualanController extends Controller
{
    public function __construct(private readonly ReturPenjualanService $returnService) {}

    public function index()
    {
        return view('medcare.menu.penjualan.returPenjualan.index', [
            'refundMethods' => ReturPenjualanService::REFUND_METHODS,
            'branches' => BranchModel::query()
                ->whereIn('id', $this->returnService->accessibleBranchIds())
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function table(Request $request)
    {
        $request->validate([
            'return_search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['draft', 'posted', 'cancelled'])],
            'branch_id' => ['nullable', 'integer'],
            'date_start' => ['nullable', 'date'],
            'date_end' => ['nullable', 'date', Rule::when($request->filled('date_start'), ['after_or_equal:date_start'])],
        ]);

        $query = ReturPenjualanModel::query()
            ->whereIn('branch_id', $this->returnService->accessibleBranchIds())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', (int) $request->branch_id))
            ->when($request->filled('date_start'), fn ($query) => $query->whereDate('tanggal_retur', '>=', $request->date_start))
            ->when($request->filled('date_end'), fn ($query) => $query->whereDate('tanggal_retur', '<=', $request->date_end));

        $search = trim((string) $request->input('return_search', $request->input('search.value', '')));

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($query) use ($like) {
                $query->where('nomor_retur', 'like', $like)
                    ->orWhere('refund_reference', 'like', $like)
                    ->orWhereHas('transaction', function ($query) use ($like) {
                        $query->where('nomor_transaksi', 'like', $like)
                            ->orWhere('customer_name', 'like', $like)
                            ->orWhere('customer_phone', 'like', $like);
                    });
            });
        }

        $metrics = (clone $query)
            ->reorder()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft")
            ->selectRaw("SUM(CASE WHEN status = 'posted' THEN 1 ELSE 0 END) as posted")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'posted' THEN grand_total ELSE 0 END), 0) as grand_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'posted' THEN total_qty ELSE 0 END), 0) as total_qty")
            ->first();

        $summary = [
            'total' => (int) ($metrics->total ?? 0),
            'draft' => (int) ($metrics->draft ?? 0),
            'posted' => (int) ($metrics->posted ?? 0),
            'cancelled' => (int) ($metrics->cancelled ?? 0),
            'grand_total' => (float) ($metrics->grand_total ?? 0),
            'total_qty' => (float) ($metrics->total_qty ?? 0),
        ];

        $query->with(['transaction:id,nomor_transaksi,customer_name', 'branch:id,name', 'createdBy:id,name'])
            ->latest('tanggal_retur')
            ->latest('id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('tanggal_retur', fn ($return) => optional($return->tanggal_retur)->format('Y-m-d'))
            ->addColumn('nomor_transaksi', fn ($return) => $return->transaction?->nomor_transaksi ?: '-')
            ->addColumn('customer_name', fn ($return) => $return->transaction?->customer_name ?: 'Umum')
            ->addColumn('branch_name', fn ($return) => $return->branch?->name ?: '-')
            ->addColumn('created_by_name', fn ($return) => $return->createdBy?->name ?: '-')
            ->addColumn('refund_method_label', fn ($return) => ReturPenjualanService::REFUND_METHODS[$return->refund_method] ?? $return->refund_method ?? '-')
            ->addColumn('status_label', fn ($return) => match ($return->status) {
                'posted' => 'Posted',
                'cancelled' => 'Dibatalkan',
                default => 'Draft',
            })
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function transactions(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json($this->returnService->transactionOptions(
            (string) $request->input('q', ''),
            (int) $request->input('limit', 50)
        ));
    }

    public function transaction($id)
    {
        $transaction = $this->returnService->findAccessibleTransaction((int) $id);

        abort_unless($transaction->status === 'completed', 404);

        return response()->json($this->returnService->transactionPayload($transaction));
    }

    public function generateNumber(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', Rule::in($this->returnService->accessibleBranchIds())],
            'tanggal_retur' => ['nullable', 'date'],
        ]);

        return response()->json([
            'number' => $this->returnService->generateNumber(
                (int) $validated['branch_id'],
                isset($validated['tanggal_retur']) ? \Illuminate\Support\Carbon::parse($validated['tanggal_retur']) : null
            ),
        ]);
    }

    public function store(Request $request)
    {
        $return = $this->returnService->createDraft($this->validatedPayload($request));

        return response()->json([
            'status' => 'success',
            'message' => 'Draft retur penjualan berhasil disimpan.',
            'return' => $this->returnPayload($return),
        ]);
    }

    public function show($id)
    {
        $return = $this->returnService->findAccessibleReturn((int) $id)->load([
            'branch',
            'transaction',
            'details.batchAllocations',
            'createdBy',
            'postedBy',
            'cancelledBy',
        ]);

        return response()->json($this->returnPayload($return));
    }

    public function edit($id)
    {
        $return = $this->returnService->findAccessibleReturn((int) $id)->load(['details.batchAllocations']);

        abort_unless($return->status === 'draft', 422, 'Hanya retur draft yang dapat diubah.');

        return response()->json([
            'return' => $this->returnPayload($return),
            'transaction' => $this->returnService->transactionPayload($return->transaction, $return->id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $return = $this->returnService->updateDraft(
            $this->returnService->findAccessibleReturn((int) $id),
            $this->validatedPayload($request)
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Draft retur penjualan berhasil diperbarui.',
            'return' => $this->returnPayload($return),
        ]);
    }

    public function post($id)
    {
        $return = $this->returnService->post($this->returnService->findAccessibleReturn((int) $id));

        return response()->json([
            'status' => 'success',
            'message' => 'Retur penjualan berhasil diposting dan stok telah dikembalikan.',
            'return' => $this->returnPayload($return),
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $return = $this->returnService->cancel(
            $this->returnService->findAccessibleReturn((int) $id),
            $validated['reason']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Retur penjualan berhasil dibatalkan.',
            'return' => $this->returnPayload($return),
        ]);
    }

    public function destroy($id)
    {
        $this->returnService->deleteDraft($this->returnService->findAccessibleReturn((int) $id));

        return response()->json([
            'status' => 'success',
            'message' => 'Draft retur penjualan berhasil dihapus.',
        ]);
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'penjualan_transaction_id' => ['required', 'integer', 'exists:penjualan_transactions,id'],
            'tanggal_retur' => ['required', 'date', 'before_or_equal:today'],
            'refund_method' => ['required', Rule::in(array_keys(ReturPenjualanService::REFUND_METHODS))],
            'refund_reference' => ['nullable', 'string', 'max:120'],
            'alasan' => ['required', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.penjualan_transaction_detail_id' => ['required', 'integer', 'distinct', 'exists:penjualan_transaction_details,id'],
            'details.*.qty' => ['required', 'numeric', 'min:0.01'],
            'details.*.alasan_item' => ['nullable', 'string', 'max:500'],
        ], [
            'details.required' => 'Pilih minimal satu item yang akan diretur.',
            'details.min' => 'Pilih minimal satu item yang akan diretur.',
            'refund_method.required' => 'Metode pengembalian dana wajib dipilih.',
            'alasan.required' => 'Alasan retur wajib diisi.',
        ]);
    }

    private function returnPayload(ReturPenjualanModel $return): array
    {
        $return->loadMissing([
            'branch',
            'transaction',
            'details.batchAllocations',
            'createdBy',
            'postedBy',
            'cancelledBy',
        ]);

        return [
            'id' => $return->id,
            'branch_id' => $return->branch_id,
            'branch' => $return->branch?->name ?: '-',
            'penjualan_transaction_id' => $return->penjualan_transaction_id,
            'nomor_transaksi' => $return->transaction?->nomor_transaksi ?: '-',
            'customer_name' => $return->transaction?->customer_name ?: 'Umum',
            'nomor_retur' => $return->nomor_retur,
            'tanggal_retur' => optional($return->tanggal_retur)->format('Y-m-d'),
            'status' => $return->status,
            'refund_method' => $return->refund_method,
            'refund_method_label' => ReturPenjualanService::REFUND_METHODS[$return->refund_method] ?? $return->refund_method ?? '-',
            'refund_reference' => $return->refund_reference,
            'total_item' => (int) $return->total_item,
            'total_qty' => (float) $return->total_qty,
            'subtotal_gross' => (float) $return->subtotal_gross,
            'diskon_item_total' => (float) $return->diskon_item_total,
            'diskon_transaksi_total' => (float) $return->diskon_transaksi_total,
            'pajak_total' => (float) $return->pajak_total,
            'grand_total' => (float) $return->grand_total,
            'alasan' => $return->alasan,
            'catatan' => $return->catatan,
            'created_by' => $return->createdBy?->name ?: '-',
            'posted_by' => $return->postedBy?->name ?: '-',
            'posted_at' => optional($return->posted_at)->format('Y-m-d H:i'),
            'cancelled_by' => $return->cancelledBy?->name ?: '-',
            'cancelled_at' => optional($return->cancelled_at)->format('Y-m-d H:i'),
            'cancellation_reason' => $return->cancellation_reason,
            'details' => $return->details->map(fn ($detail) => [
                'id' => $detail->id,
                'penjualan_transaction_detail_id' => $detail->penjualan_transaction_detail_id,
                'kode_obat' => $detail->kode_obat,
                'nama_obat' => $detail->nama_obat,
                'satuan_jual' => $detail->satuan_jual,
                'satuan_stok' => $detail->satuan_stok,
                'konversi' => (float) $detail->konversi,
                'qty_jual' => (float) $detail->qty_jual,
                'qty_stok' => (float) $detail->qty_stok,
                'harga_jual' => (float) $detail->harga_jual,
                'subtotal_gross' => (float) $detail->subtotal_gross,
                'diskon_item_nominal' => (float) $detail->diskon_item_nominal,
                'diskon_transaksi_nominal' => (float) $detail->diskon_transaksi_nominal,
                'pajak_nominal' => (float) $detail->pajak_nominal,
                'total' => (float) $detail->total,
                'alasan_item' => $detail->alasan_item,
                'batches' => $detail->batchAllocations->map(fn ($batch) => [
                    'id' => $batch->id,
                    'stok_batch_id' => $batch->stok_batch_id,
                    'no_batch' => $batch->no_batch,
                    'expired_date' => optional($batch->expired_date)->format('Y-m-d'),
                    'qty_stok' => (float) $batch->qty_stok,
                    'kartu_stok_id' => $batch->kartu_stok_id,
                    'cancel_kartu_stok_id' => $batch->cancel_kartu_stok_id,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
