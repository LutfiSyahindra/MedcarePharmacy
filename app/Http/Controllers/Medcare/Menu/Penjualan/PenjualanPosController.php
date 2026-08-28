<?php

namespace App\Http\Controllers\Medcare\Menu\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\PatientModel;
use App\Services\Menu\Penjualan\CashierShiftService;
use App\Services\Menu\Penjualan\PenjualanPosService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PenjualanPosController extends Controller
{
    public function __construct(
        private readonly PenjualanPosService $posService,
        private readonly CashierShiftService $cashierShiftService
    ) {}

    public function index(Request $request)
    {
        $branches = $this->posService->activeBranches($request->user());
        $branches->load('apotekProfile:id,branch_id,name,logo_path,operational_hours');
        $branches->each(function (BranchModel $branch) use ($request) {
            $branch->setAttribute('display_name', $branch->apotekProfile?->name ?: $branch->name);
            $branch->setAttribute('logo_url', $branch->apotekProfile?->logo_url ?: asset('assets/apotek/LogoResmi.png'));
            $branch->setAttribute('operational', $this->cashierShiftService->operationalState($branch));
            $branch->setAttribute('cashier_shift', $this->cashierShiftService->payload(
                $this->cashierShiftService->currentShift($request->user(), $branch->id)
            ));
        });
        $canSwitchBranch = $branches->count() > 1;

        return view('medcare.menu.penjualan.pos.pos', [
            'transactionTypes' => PenjualanPosService::TRANSACTION_TYPES,
            'paymentMethods' => PenjualanPosService::PAYMENT_METHODS,
            'canSwitchPosBranch' => $canSwitchBranch,
            'posBranches' => $branches,
            'selectedPosBranchId' => $canSwitchBranch ? null : $branches->first()?->id,
        ]);
    }

    public function history()
    {
        return view('medcare.menu.penjualan.pos.history', [
            'transactionTypes' => PenjualanPosService::TRANSACTION_TYPES,
            'branches' => BranchModel::query()
                ->whereIn('id', $this->posService->transactionBranchIds())
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function products(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
        ]);

        return response()->json($this->posService->searchProducts(
            (string) $request->input('q', ''),
            (int) $request->input('limit', 30),
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null
        ));
    }

    public function patients(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json([
            'results' => $this->posService->searchPatients(
                (string) ($validated['q'] ?? ''),
                isset($validated['branch_id']) ? (int) $validated['branch_id'] : null
            ),
        ]);
    }

    public function quote(Request $request)
    {
        $validated = $request->validate([
            'obat_id' => ['required', 'integer', 'exists:master_obats,id'],
            'satuan_id' => ['nullable', 'integer', 'exists:satuans,id'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        return response()->json($this->posService->productQuote(
            (int) $validated['obat_id'],
            isset($validated['satuan_id']) ? (int) $validated['satuan_id'] : null,
            (float) $validated['qty'],
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null
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
            ->whereIn('branch_id', $this->posService->transactionBranchIds())
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
            ->withCount([
                'details',
                'salesReturns as active_sales_returns_count' => fn ($query) => $query->whereIn('status', ['draft', 'posted']),
            ])
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
            ->addColumn('can_print_labels', fn ($transaction) => $this->canPrintLabels($transaction))
            ->addColumn('has_active_sales_return', fn ($transaction) => (int) ($transaction->active_sales_returns_count ?? 0) > 0)
            ->addColumn('can_cancel', fn ($transaction) => in_array($transaction->status, ['draft', 'completed'], true)
                && (int) ($transaction->active_sales_returns_count ?? 0) === 0)
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

    public function receipt(Request $request, $id)
    {
        $transaction = $this->findScopedTransaction($id)->load([
            'branch.apotekProfile',
            'details.batchAllocations',
            'payments',
            'createdBy',
            'completedBy',
        ]);

        return view('medcare.menu.penjualan.pos.receipt', [
            'transaction' => $transaction,
            'apotekProfile' => $transaction->branch?->apotekProfile,
            'transactionTypes' => PenjualanPosService::TRANSACTION_TYPES,
            'paymentMethods' => PenjualanPosService::PAYMENT_METHODS,
            'embedded' => $request->boolean('embedded'),
            'autoPrint' => $request->boolean('autoprint', ! $request->boolean('embedded')),
            'asNota' => $request->boolean('nota'),
        ]);
    }

    public function labels(Request $request, $id)
    {
        $transaction = $this->findScopedTransaction($id)->load([
            'branch.apotekProfile',
            'details',
            'createdBy',
            'completedBy',
        ]);

        abort_unless($this->canPrintLabels($transaction), 404);

        return view('medcare.menu.penjualan.pos.labels', [
            'transaction' => $transaction,
            'apotekProfile' => $transaction->branch?->apotekProfile,
            'embedded' => $request->boolean('embedded'),
            'autoPrint' => $request->boolean('autoprint', ! $request->boolean('embedded')),
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
        if ($request->filled('customer_phone')) {
            $request->merge([
                'customer_phone' => PatientModel::normalizePhone($request->input('customer_phone')),
            ]);
        }

        $typeKeys = array_keys(PenjualanPosService::TRANSACTION_TYPES);
        $paymentKeys = array_keys(PenjualanPosService::PAYMENT_METHODS);
        $isPrescription = in_array($request->input('jenis_transaksi'), ['penjualan_resep', 'penjualan_racikan'], true);
        $isCompoundPrescription = $request->input('jenis_transaksi') === 'penjualan_racikan';
        $requiredCustomer = Rule::requiredIf(! $draft);
        $requiredPrescription = Rule::requiredIf(! $draft && $isPrescription);
        $requiredCompound = Rule::requiredIf(! $draft && $isCompoundPrescription);

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'draft_id' => ['nullable', 'integer', 'exists:penjualan_transactions,id'],
            'tanggal_transaksi' => ['nullable', 'date'],
            'jenis_transaksi' => ['required', Rule::in($typeKeys)],
            'customer_name' => [$requiredCustomer, 'nullable', 'string', 'max:150'],
            'customer_phone' => [$requiredCustomer, 'nullable', 'digits_between:8,15'],
            'patient_id' => ['nullable', 'integer'],
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
            'details.*.durasi_hari' => [$requiredCompound, 'nullable', 'integer', 'min:1', 'max:3650'],
            'details.*.racikan_group' => [$requiredCompound, 'nullable', 'string', 'max:80'],
            'details.*.bentuk_racikan' => [$requiredCompound, 'nullable', 'string', 'max:80'],
            'details.*.jumlah_racikan' => [$requiredCompound, 'nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'details.*.jumlah_ambil_resep' => [$requiredCompound, 'nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'details.*.signa_1' => [$requiredCompound, 'nullable', 'string', 'max:50'],
            'details.*.signa_2' => [$requiredCompound, 'nullable', 'string', 'max:50'],
            'details.*.embalase_racikan' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'details.*.dosis_komponen' => [$requiredCompound, 'nullable', 'string', 'max:100'],
            'details.*.kekuatan_obat' => ['nullable', 'string', 'max:100'],
            'details.*.jumlah_resep' => [$requiredCompound, 'nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'payments' => [$draft ? 'nullable' : 'required', 'array'],
            'payments.*.metode' => ['nullable', Rule::in($paymentKeys)],
            'payments.*.amount' => ['nullable', 'numeric', 'min:0'],
            'payments.*.reference_no' => ['nullable', 'string', 'max:120'],
            'payments.*.catatan' => ['nullable', 'string'],
        ], [
            'customer_name.required' => 'Nama pembeli wajib diisi sebelum transaksi diselesaikan.',
            'customer_phone.required' => 'Nomor HP pembeli wajib diisi sebelum transaksi diselesaikan.',
            'customer_phone.digits_between' => 'Nomor HP pembeli harus terdiri dari 8 sampai 15 angka.',
            'nomor_resep.required' => 'Nomor resep wajib diisi.',
            'tanggal_resep.required' => 'Tanggal resep wajib diisi.',
            'dokter_name.required' => 'Nama dokter penulis resep wajib diisi.',
            'details.*.aturan_pakai.required' => 'Aturan pakai wajib diisi untuk setiap obat resep.',
            'details.*.durasi_hari.required' => 'JHO wajib diisi untuk setiap racikan.',
            'details.*.racikan_group.required' => 'Kelompok R/ wajib diisi untuk setiap komponen racikan.',
            'details.*.bentuk_racikan.required' => 'Bentuk racikan wajib dipilih.',
            'details.*.jumlah_racikan.required' => 'Jumlah racikan wajib diisi.',
            'details.*.jumlah_ambil_resep.required' => 'Jumlah ambil resep wajib diisi.',
            'details.*.signa_1.required' => 'Signa 1 wajib diisi.',
            'details.*.signa_2.required' => 'Signa 2 wajib diisi.',
            'details.*.dosis_komponen.required' => 'Dosis komponen wajib diisi untuk setiap obat racikan.',
            'details.*.jumlah_resep.required' => 'Jumlah resep wajib diisi untuk setiap obat racikan.',
        ]);

        $validated['branch_id'] = $this->posService->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $request->user()
        );

        if (! empty($validated['patient_id'])) {
            $patient = PatientModel::query()
                ->whereKey($validated['patient_id'])
                ->where('branch_id', $validated['branch_id'])
                ->first();

            if (! $patient) {
                throw ValidationException::withMessages([
                    'patient_id' => 'Pasien tidak ditemukan pada cabang POS yang aktif.',
                ]);
            }

            $validated['customer_name'] = $patient->name;
            $validated['customer_phone'] = $patient->phone;
        }

        return $validated;
    }

    private function findScopedTransaction($id): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::whereIn('branch_id', $this->posService->transactionBranchIds())
            ->findOrFail($id);
    }

    private function canPrintLabels(PenjualanTransactionModel $transaction): bool
    {
        return $transaction->status === 'completed'
            && in_array($transaction->jenis_transaksi, ['penjualan_resep', 'penjualan_racikan'], true);
    }

    private function transactionPayload(PenjualanTransactionModel $transaction): array
    {
        $transaction->loadMissing([
            'details.obat.satuan',
            'details.obat.konversiSatuan.satuan',
        ]);
        $hasActiveSalesReturn = $transaction->salesReturns()
            ->whereIn('status', ['draft', 'posted'])
            ->exists();

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
            'has_active_sales_return' => $hasActiveSalesReturn,
            'can_cancel' => in_array($transaction->status, ['draft', 'completed'], true) && ! $hasActiveSalesReturn,
            'can_print_labels' => $this->canPrintLabels($transaction),
            'labels_url' => $this->canPrintLabels($transaction)
                ? route('penjualan.pos.labels', $transaction->id)
                : null,
            'customer_name' => $transaction->customer_name,
            'customer_phone' => $transaction->customer_phone,
            'patient_id' => $transaction->patient_id,
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
                'units' => $detail->obat
                    ? $this->posService->unitsForProduct($detail->obat)
                    : [],
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
                'bentuk_racikan' => $detail->bentuk_racikan,
                'jumlah_racikan' => $detail->jumlah_racikan !== null ? (float) $detail->jumlah_racikan : null,
                'jumlah_ambil_resep' => $detail->jumlah_ambil_resep !== null ? (float) $detail->jumlah_ambil_resep : null,
                'signa_1' => $detail->signa_1,
                'signa_2' => $detail->signa_2,
                'embalase_racikan' => (float) $detail->embalase_racikan,
                'dosis_komponen' => $detail->dosis_komponen,
                'kekuatan_obat' => $detail->kekuatan_obat,
                'jumlah_resep' => $detail->jumlah_resep !== null ? (float) $detail->jumlah_resep : null,
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
