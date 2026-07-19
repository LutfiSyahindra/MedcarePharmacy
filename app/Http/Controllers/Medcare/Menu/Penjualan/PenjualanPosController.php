<?php

namespace App\Http\Controllers\Medcare\Menu\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Services\Menu\Penjualan\PenjualanPosService;
use App\Support\BranchAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PenjualanPosController extends Controller
{
    public function __construct(private readonly PenjualanPosService $posService) {}

    public function index()
    {
        return view('medcare.menu.penjualan.pos.pos', [
            'transactionTypes' => PenjualanPosService::TRANSACTION_TYPES,
            'paymentMethods' => PenjualanPosService::PAYMENT_METHODS,
        ]);
    }

    public function history()
    {
        return view('medcare.menu.penjualan.pos.history', [
            'transactionTypes' => PenjualanPosService::TRANSACTION_TYPES,
            'branches' => BranchModel::query()
                ->whereIn('id', BranchAccess::userBranchIds())
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function products(Request $request)
    {
        return response()->json($this->posService->searchProducts(
            (string) $request->input('q', ''),
            (int) $request->input('limit', 30)
        ));
    }

    public function quote(Request $request)
    {
        $validated = $request->validate([
            'obat_id' => ['required', 'integer', 'exists:master_obats,id'],
            'satuan_id' => ['nullable', 'integer', 'exists:satuans,id'],
            'qty' => ['required', 'numeric', 'min:0.01'],
        ]);

        return response()->json($this->posService->productQuote(
            (int) $validated['obat_id'],
            isset($validated['satuan_id']) ? (int) $validated['satuan_id'] : null,
            (float) $validated['qty']
        ));
    }

    public function storeDraft(Request $request)
    {
        $transaction = $this->posService->saveDraft($this->validatedTransactionPayload($request, true));

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi sementara berhasil disimpan.',
            'transaction' => $this->transactionPayload($transaction),
        ]);
    }

    public function complete(Request $request)
    {
        $transaction = $this->posService->completeTransaction($this->validatedTransactionPayload($request, false));

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi penjualan berhasil disimpan.',
            'transaction' => $this->transactionPayload($transaction),
            'receipt_url' => route('penjualan.pos.receipt', $transaction->id),
        ]);
    }

    public function table(Request $request)
    {
        $request->validate([
            'history_search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['draft', 'completed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'paid', 'credit', 'void'])],
            'jenis_transaksi' => ['nullable', Rule::in(array_keys(PenjualanPosService::TRANSACTION_TYPES))],
            'branch_id' => ['nullable', 'integer'],
            'date_start' => ['nullable', 'date'],
            'date_end' => [
                'nullable',
                'date',
                Rule::when($request->filled('date_start'), ['after_or_equal:date_start']),
            ],
        ]);

        $query = PenjualanTransactionModel::query()
            ->whereIn('branch_id', BranchAccess::userBranchIds())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->payment_status))
            ->when($request->filled('jenis_transaksi'), fn ($query) => $query->where('jenis_transaksi', $request->jenis_transaksi))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', (int) $request->branch_id))
            ->when($request->filled('date_start'), fn ($query) => $query->whereDate('tanggal_transaksi', '>=', $request->date_start))
            ->when($request->filled('date_end'), fn ($query) => $query->whereDate('tanggal_transaksi', '<=', $request->date_end));

        $search = trim((string) $request->input('history_search', $request->input('search.value', '')));

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where('nomor_transaksi', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like)
                    ->orWhere('nomor_resep', 'like', $like)
                    ->orWhere('dokter_name', 'like', $like)
                    ->orWhere('asal_resep', 'like', $like)
                    ->orWhere('instansi_name', 'like', $like)
                    ->orWhereHas('createdBy', fn ($query) => $query->where('name', 'like', $like));
            });
        }

        $metrics = (clone $query)
            ->reorder()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN grand_total ELSE 0 END), 0) as grand_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total_bayar ELSE 0 END), 0) as total_bayar")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN sisa_tagihan ELSE 0 END), 0) as sisa_tagihan")
            ->first();

        $completed = (int) ($metrics->completed ?? 0);
        $grandTotal = (float) ($metrics->grand_total ?? 0);
        $summary = [
            'total' => (int) ($metrics->total ?? 0),
            'draft' => (int) ($metrics->draft ?? 0),
            'completed' => $completed,
            'cancelled' => (int) ($metrics->cancelled ?? 0),
            'grand_total' => $grandTotal,
            'total_bayar' => (float) ($metrics->total_bayar ?? 0),
            'sisa_tagihan' => (float) ($metrics->sisa_tagihan ?? 0),
            'average_ticket' => $completed > 0 ? $grandTotal / $completed : 0,
        ];

        $query->with(['branch', 'createdBy'])
            ->withCount('details')
            ->latest('tanggal_transaksi')
            ->latest('id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('tanggal_transaksi', fn ($transaction) => optional($transaction->tanggal_transaksi)->format('Y-m-d H:i'))
            ->editColumn('customer_name', fn ($transaction) => $transaction->customer_name ?: 'Umum')
            ->addColumn('jenis_label', fn ($transaction) => PenjualanPosService::TRANSACTION_TYPES[$transaction->jenis_transaksi] ?? $transaction->jenis_transaksi)
            ->addColumn('status_label', fn ($transaction) => match ($transaction->status) {
                'completed' => 'Selesai',
                'cancelled' => 'Batal',
                default => 'Sementara',
            })
            ->addColumn('payment_label', fn ($transaction) => match ($transaction->payment_status) {
                'paid' => 'Lunas',
                'credit' => 'Tagihan',
                'void' => 'Void',
                default => 'Belum Bayar',
            })
            ->addColumn('branch_name', fn ($transaction) => $transaction->branch->name ?? '-')
            ->addColumn('cashier_name', fn ($transaction) => $transaction->createdBy->name ?? '-')
            ->addColumn('item_count', fn ($transaction) => (int) ($transaction->details_count ?? 0))
            ->addColumn('can_resume', fn ($transaction) => $transaction->status === 'draft')
            ->addColumn('can_print', fn ($transaction) => $transaction->status === 'completed')
            ->addColumn('can_cancel', fn ($transaction) => in_array($transaction->status, ['draft', 'completed'], true))
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function show($id)
    {
        $transaction = $this->findScopedTransaction($id)->load([
            'branch',
            'details.batchAllocations',
            'payments',
            'createdBy',
            'completedBy',
            'cancelledBy',
        ]);

        return response()->json($this->transactionPayload($transaction));
    }

    public function receipt($id)
    {
        $transaction = $this->findScopedTransaction($id)->load([
            'branch',
            'details.batchAllocations',
            'payments',
            'createdBy',
            'completedBy',
        ]);

        return view('medcare.menu.penjualan.pos.receipt', [
            'transaction' => $transaction,
            'transactionTypes' => PenjualanPosService::TRANSACTION_TYPES,
            'paymentMethods' => PenjualanPosService::PAYMENT_METHODS,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $transaction = $this->posService->cancelTransaction(
            $this->findScopedTransaction($id),
            $validated['reason']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil dibatalkan.',
            'transaction' => $this->transactionPayload($transaction),
        ]);
    }

    private function validatedTransactionPayload(Request $request, bool $draft): array
    {
        $typeKeys = array_keys(PenjualanPosService::TRANSACTION_TYPES);
        $paymentKeys = array_keys(PenjualanPosService::PAYMENT_METHODS);
        $isPrescription = in_array($request->input('jenis_transaksi'), ['penjualan_resep', 'penjualan_racikan'], true);
        $isCompoundPrescription = $request->input('jenis_transaksi') === 'penjualan_racikan';
        $requiredPrescription = Rule::requiredIf(! $draft && $isPrescription);
        $requiredCompound = Rule::requiredIf(! $draft && $isCompoundPrescription);

        return $request->validate([
            'draft_id' => ['nullable', 'integer', 'exists:penjualan_transactions,id'],
            'tanggal_transaksi' => ['nullable', 'date'],
            'jenis_transaksi' => ['required', Rule::in($typeKeys)],
            'customer_name' => [$requiredPrescription, 'nullable', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'nomor_resep' => [$requiredPrescription, 'nullable', 'string', 'max:100'],
            'tanggal_resep' => [$requiredPrescription, 'nullable', 'date'],
            'dokter_name' => [$requiredPrescription, 'nullable', 'string', 'max:150'],
            'asal_resep' => ['nullable', 'string', 'max:150'],
            'instansi_name' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string'],
            'diskon_transaksi_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'diskon_transaksi_nominal' => ['nullable', 'numeric', 'min:0'],
            'embalase' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'use_pajak' => ['nullable', 'boolean'],
            'pajak_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.obat_id' => ['required', 'integer', 'exists:master_obats,id'],
            'details.*.satuan_id' => ['nullable', 'integer', 'exists:satuans,id'],
            'details.*.qty' => ['required', 'numeric', 'min:0.01'],
            'details.*.diskon_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'details.*.diskon_nominal' => ['nullable', 'numeric', 'min:0'],
            'details.*.keterangan' => ['nullable', 'string'],
            'details.*.aturan_pakai' => [$requiredPrescription, 'nullable', 'string', 'max:255'],
            'details.*.waktu_konsumsi' => ['nullable', 'string', 'max:80'],
            'details.*.durasi_hari' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'details.*.racikan_group' => [$requiredCompound, 'nullable', 'string', 'max:80'],
            'details.*.dosis_komponen' => [$requiredCompound, 'nullable', 'string', 'max:100'],
            'payments' => [$draft ? 'nullable' : 'required', 'array'],
            'payments.*.metode' => ['nullable', Rule::in($paymentKeys)],
            'payments.*.amount' => ['nullable', 'numeric', 'min:0'],
            'payments.*.reference_no' => ['nullable', 'string', 'max:120'],
            'payments.*.catatan' => ['nullable', 'string'],
        ], [
            'customer_name.required' => 'Nama pasien wajib diisi untuk transaksi resep.',
            'nomor_resep.required' => 'Nomor resep wajib diisi.',
            'tanggal_resep.required' => 'Tanggal resep wajib diisi.',
            'dokter_name.required' => 'Nama dokter penulis resep wajib diisi.',
            'details.*.aturan_pakai.required' => 'Aturan pakai wajib diisi untuk setiap obat resep.',
            'details.*.racikan_group.required' => 'Kelompok R/ wajib diisi untuk setiap komponen racikan.',
            'details.*.dosis_komponen.required' => 'Dosis komponen wajib diisi untuk setiap obat racikan.',
        ]);
    }

    private function findScopedTransaction($id): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::whereIn('branch_id', BranchAccess::userBranchIds())
            ->findOrFail($id);
    }

    private function transactionPayload(PenjualanTransactionModel $transaction): array
    {
        return [
            'id' => $transaction->id,
            'branch_id' => $transaction->branch_id,
            'branch' => $transaction->branch->name ?? '-',
            'nomor_transaksi' => $transaction->nomor_transaksi,
            'tanggal_transaksi' => optional($transaction->tanggal_transaksi)->format('Y-m-d H:i'),
            'jenis_transaksi' => $transaction->jenis_transaksi,
            'jenis_label' => PenjualanPosService::TRANSACTION_TYPES[$transaction->jenis_transaksi] ?? $transaction->jenis_transaksi,
            'status' => $transaction->status,
            'payment_status' => $transaction->payment_status,
            'customer_name' => $transaction->customer_name,
            'customer_phone' => $transaction->customer_phone,
            'nomor_resep' => $transaction->nomor_resep,
            'tanggal_resep' => optional($transaction->tanggal_resep)->format('Y-m-d'),
            'dokter_name' => $transaction->dokter_name,
            'asal_resep' => $transaction->asal_resep,
            'instansi_name' => $transaction->instansi_name,
            'catatan' => $transaction->catatan,
            'subtotal_gross' => (float) $transaction->subtotal_gross,
            'diskon_item_total' => (float) $transaction->diskon_item_total,
            'diskon_transaksi_percent' => (float) $transaction->diskon_transaksi_percent,
            'diskon_transaksi_nominal' => (float) $transaction->diskon_transaksi_nominal,
            'subtotal_net' => (float) $transaction->subtotal_net,
            'embalase' => (float) $transaction->embalase,
            'pajak_percent' => (float) $transaction->pajak_percent,
            'pajak_total' => (float) $transaction->pajak_total,
            'grand_total' => (float) $transaction->grand_total,
            'total_bayar' => (float) $transaction->total_bayar,
            'kembalian' => (float) $transaction->kembalian,
            'sisa_tagihan' => (float) $transaction->sisa_tagihan,
            'created_by' => $transaction->createdBy->name ?? '-',
            'completed_by' => $transaction->completedBy->name ?? '-',
            'cancelled_by' => $transaction->cancelledBy->name ?? '-',
            'cancelled_at' => optional($transaction->cancelled_at)->format('Y-m-d H:i'),
            'cancellation_reason' => $transaction->cancellation_reason,
            'details' => $transaction->details->map(fn ($detail) => [
                'id' => $detail->id,
                'obat_id' => $detail->obat_id,
                'satuan_id' => $detail->satuan_id,
                'kode_obat' => $detail->kode_obat,
                'nama_obat' => $detail->nama_obat,
                'satuan_jual' => $detail->satuan_jual,
                'satuan_stok' => $detail->satuan_stok,
                'konversi' => (float) $detail->konversi,
                'qty_jual' => (float) $detail->qty_jual,
                'qty_stok' => (float) $detail->qty_stok,
                'harga_jual' => (float) $detail->harga_jual,
                'subtotal_gross' => (float) $detail->subtotal_gross,
                'diskon_percent' => (float) $detail->diskon_percent,
                'diskon_nominal' => (float) $detail->diskon_nominal,
                'subtotal_net' => (float) $detail->subtotal_net,
                'total_line' => (float) $detail->total_line,
                'batch_summary' => $detail->batch_summary ?: $detail->batchAllocations->map(fn ($allocation) => [
                    'stok_batch_id' => $allocation->stok_batch_id,
                    'no_batch' => $allocation->no_batch,
                    'expired_date' => optional($allocation->expired_date)->format('Y-m-d'),
                    'qty_stok' => (float) $allocation->qty_stok,
                    'harga_jual' => (float) $allocation->harga_jual,
                    'subtotal_gross' => (float) $allocation->subtotal_gross,
                ])->values()->all(),
                'keterangan' => $detail->keterangan,
                'aturan_pakai' => $detail->aturan_pakai,
                'waktu_konsumsi' => $detail->waktu_konsumsi,
                'durasi_hari' => $detail->durasi_hari,
                'racikan_group' => $detail->racikan_group,
                'dosis_komponen' => $detail->dosis_komponen,
            ])->values()->all(),
            'payments' => $transaction->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'metode' => $payment->metode,
                'metode_label' => PenjualanPosService::PAYMENT_METHODS[$payment->metode] ?? $payment->metode,
                'amount' => (float) $payment->amount,
                'reference_no' => $payment->reference_no,
                'catatan' => $payment->catatan,
                'paid_at' => optional($payment->paid_at)->format('Y-m-d H:i'),
            ])->values()->all(),
        ];
    }
}
