<?php

namespace App\Http\Controllers\Medcare\Menu\Dokumen;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanPaymentModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\User;
use App\Services\Menu\Penjualan\PenjualanPosService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReceiptDocumentController extends Controller
{
    public function __construct(private readonly PenjualanPosService $posService) {}

    /**
     * @return array<int, string>
     */
    public static function templateTypes(): array
    {
        return array_keys(PenjualanPosService::TRANSACTION_TYPES);
    }

    public function index(Request $request): View
    {
        $branches = $this->accessibleBranches();
        $requestedBranchId = $request->integer('branch_id');
        $activeBranchId = (int) $request->session()->get('active_branch_id', 0);
        $selectedBranch = $branches->firstWhere('id', $requestedBranchId)
            ?: $branches->firstWhere('id', $activeBranchId)
            ?: $branches->first();

        return view('medcare.menu.dokumen.nota', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'transactionTypes' => PenjualanPosService::TRANSACTION_TYPES,
        ]);
    }

    public function template(Request $request, string $type): View
    {
        abort_unless(in_array($type, self::templateTypes(), true), 404);

        $branches = $this->accessibleBranches();
        $requestedBranchId = $request->integer('branch_id');

        if ($requestedBranchId > 0) {
            $branch = $branches->firstWhere('id', $requestedBranchId);
            abort_if(! $branch, 404, 'Cabang tidak ditemukan atau tidak dapat diakses.');
        } else {
            $activeBranchId = (int) $request->session()->get('active_branch_id', 0);
            $branch = $branches->firstWhere('id', $activeBranchId) ?: $branches->first();
        }

        abort_if(! $branch, 422, 'User belum memiliki akses cabang untuk membuat template nota.');

        return view('medcare.menu.penjualan.pos.receipt', [
            'transaction' => $this->templateTransaction($branch, $type),
            'apotekProfile' => $branch->apotekProfile,
            'transactionTypes' => PenjualanPosService::TRANSACTION_TYPES,
            'paymentMethods' => PenjualanPosService::PAYMENT_METHODS,
            'embedded' => false,
            'autoPrint' => $request->boolean('print'),
            'isTemplate' => true,
        ]);
    }

    public function table(Request $request): JsonResponse
    {
        $branchIds = $this->posService->transactionBranchIds();
        $filters = $request->validate([
            'nota_search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(self::templateTypes())],
            'branch_id' => ['nullable', 'integer', Rule::in($branchIds)],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_start'],
        ]);

        $summaryQuery = $this->filteredQuery($branchIds, $filters);
        $summaryRows = (clone $summaryQuery)
            ->select('jenis_transaksi')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as grand_total')
            ->groupBy('jenis_transaksi')
            ->get();
        $summary = $this->summary($summaryRows);
        $query = $summaryQuery
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('jenis_transaksi', $type))
            ->with([
                'branch:id,name',
                'createdBy:id,name',
                'details:id,penjualan_transaction_id,nama_obat',
            ])
            ->latest('tanggal_transaksi')
            ->latest('id');

        return DataTables::eloquent($query)
            ->editColumn('tanggal_transaksi', fn (PenjualanTransactionModel $transaction) => $transaction->tanggal_transaksi?->format('Y-m-d H:i'))
            ->editColumn('customer_name', fn (PenjualanTransactionModel $transaction) => $transaction->customer_name ?: 'Umum')
            ->addColumn('type_label', fn (PenjualanTransactionModel $transaction) => PenjualanPosService::TRANSACTION_TYPES[$transaction->jenis_transaksi] ?? $transaction->jenis_transaksi)
            ->addColumn('branch_name', fn (PenjualanTransactionModel $transaction) => $transaction->branch?->name ?: '-')
            ->addColumn('cashier_name', fn (PenjualanTransactionModel $transaction) => $transaction->createdBy?->name ?: '-')
            ->addColumn('item_count', fn (PenjualanTransactionModel $transaction) => $transaction->details->count())
            ->addColumn('medicine_names', fn (PenjualanTransactionModel $transaction) => $transaction->details
                ->pluck('nama_obat')
                ->filter()
                ->unique()
                ->values()
                ->implode(', '))
            ->addColumn('payment_label', fn (PenjualanTransactionModel $transaction) => match ($transaction->payment_status) {
                'paid' => 'Lunas',
                'credit' => 'Kredit',
                'void' => 'Void',
                default => 'Belum Lunas',
            })
            ->addColumn('preview_url', fn (PenjualanTransactionModel $transaction) => route('penjualan.pos.receipt', [
                'id' => $transaction->id,
                'autoprint' => 0,
                'nota' => 1,
            ]))
            ->addColumn('print_url', fn (PenjualanTransactionModel $transaction) => route('penjualan.pos.receipt', [
                'id' => $transaction->id,
                'autoprint' => 1,
                'nota' => 1,
            ]))
            ->with(['summary' => $summary])
            ->make(true);
    }

    /**
     * @param  array<int, int>  $branchIds
     * @param  array<string, mixed>  $filters
     */
    private function filteredQuery(array $branchIds, array $filters): Builder
    {
        $query = PenjualanTransactionModel::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed')
            ->when($filters['branch_id'] ?? null, fn (Builder $query, int $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['date_start'] ?? null, fn (Builder $query, string $date) => $query->whereDate('tanggal_transaksi', '>=', $date))
            ->when($filters['date_end'] ?? null, fn (Builder $query, string $date) => $query->whereDate('tanggal_transaksi', '<=', $date));

        $search = trim((string) ($filters['nota_search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $like = '%'.$search.'%';
                $query->where('nomor_transaksi', 'like', $like)
                    ->orWhere('nomor_resep', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like)
                    ->orWhere('dokter_name', 'like', $like)
                    ->orWhere('instansi_name', 'like', $like)
                    ->orWhereHas('branch', fn (Builder $query) => $query->where('name', 'like', $like))
                    ->orWhereHas('details', fn (Builder $query) => $query->where('nama_obat', 'like', $like));
            });
        }

        return $query;
    }

    /**
     * @param  Collection<int, PenjualanTransactionModel>  $transactions
     * @return array<string, int|float>
     */
    private function summary(Collection $transactions): array
    {
        $counts = $transactions->pluck('total', 'jenis_transaksi');

        return [
            'total' => (int) $transactions->sum('total'),
            'grand_total' => (float) $transactions->sum('grand_total'),
            'types' => collect(self::templateTypes())
                ->mapWithKeys(fn (string $type) => [$type => (int) $counts->get($type, 0)])
                ->all(),
        ];
    }

    private function accessibleBranches(): Collection
    {
        return BranchModel::query()
            ->with('apotekProfile')
            ->whereIn('id', $this->posService->transactionBranchIds())
            ->orderBy('name')
            ->get();
    }

    private function templateTransaction(BranchModel $branch, string $type): PenjualanTransactionModel
    {
        $isPrescription = in_array($type, ['penjualan_resep', 'penjualan_racikan'], true);
        $isCompound = $type === 'penjualan_racikan';
        $isCredit = $type === 'penjualan_kredit';
        $isInstitution = $type === 'penjualan_instansi';

        $transaction = (new PenjualanTransactionModel)->forceFill([
            'nomor_transaksi' => 'TEMPLATE-NOTA-'.strtoupper(str_replace('penjualan_', '', $type)),
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => $type,
            'status' => 'completed',
            'payment_status' => $isCredit ? 'credit' : 'paid',
            'customer_name' => $isInstitution ? '[Nama instansi / pelanggan]' : ($isPrescription ? '[Nama pasien]' : '[Nama pelanggan]'),
            'customer_phone' => '[Nomor telepon]',
            'nomor_resep' => $isPrescription ? '[Nomor resep]' : null,
            'tanggal_resep' => $isPrescription ? now()->toDateString() : null,
            'dokter_name' => $isPrescription ? '[Nama dokter]' : null,
            'asal_resep' => $isPrescription ? '[Asal resep]' : null,
            'instansi_name' => $isInstitution ? '[Nama instansi]' : null,
            'subtotal_gross' => 125000,
            'diskon_item_total' => 0,
            'diskon_transaksi_nominal' => 0,
            'subtotal_net' => 125000,
            'embalase' => $isCompound ? 10000 : 0,
            'pajak_total' => 0,
            'grand_total' => $isCompound ? 135000 : 125000,
            'total_bayar' => $isCredit ? 25000 : ($isCompound ? 135000 : 125000),
            'sisa_tagihan' => $isCredit ? 100000 : 0,
            'kembalian' => 0,
            'catatan' => $isCredit ? '[Jatuh tempo / catatan kredit]' : null,
        ]);

        $details = $isCompound
            ? collect([
                $this->templateDetail('[Komponen racikan 1]', 1, 50000, [
                    'racikan_group' => 'R/ 1',
                    'bentuk_racikan' => '[Bentuk racikan]',
                    'jumlah_racikan' => 10,
                    'jumlah_ambil_resep' => 10,
                    'signa_1' => 3,
                    'signa_2' => 1,
                    'durasi_hari' => 3,
                    'aturan_pakai' => '[Aturan pakai racikan]',
                    'dosis_komponen' => '[Dosis komponen]',
                    'kekuatan_obat' => '[Kekuatan]',
                    'jumlah_resep' => 1,
                    'embalase_racikan' => 10000,
                ]),
                $this->templateDetail('[Komponen racikan 2]', 1, 75000, [
                    'racikan_group' => 'R/ 1',
                    'bentuk_racikan' => '[Bentuk racikan]',
                    'jumlah_racikan' => 10,
                    'jumlah_ambil_resep' => 10,
                    'signa_1' => 3,
                    'signa_2' => 1,
                    'durasi_hari' => 3,
                    'aturan_pakai' => '[Aturan pakai racikan]',
                    'dosis_komponen' => '[Dosis komponen]',
                    'kekuatan_obat' => '[Kekuatan]',
                    'jumlah_resep' => 1,
                    'embalase_racikan' => 10000,
                ]),
            ])
            : collect([
                $this->templateDetail($isPrescription ? '[Nama obat resep]' : '[Nama produk / obat]', 1, 125000, $isPrescription ? [
                    'aturan_pakai' => '[Aturan pakai]',
                    'waktu_konsumsi' => 'sesudah_makan',
                    'durasi_hari' => 3,
                    'keterangan' => '[Catatan penggunaan]',
                ] : []),
            ]);

        $payment = (new PenjualanPaymentModel)->forceFill([
            'metode' => $isInstitution ? 'instansi' : ($isCredit ? 'piutang' : 'tunai'),
            'amount' => (float) $transaction->total_bayar,
            'reference_no' => $isInstitution ? '[Nomor referensi instansi]' : null,
        ]);

        $transaction->setRelation('branch', $branch);
        $transaction->setRelation('details', $details);
        $transaction->setRelation('payments', collect([$payment]));
        $transaction->setRelation('createdBy', (new User)->forceFill(['name' => '[Nama kasir]']));
        $transaction->setRelation('completedBy', null);

        return $transaction;
    }

    private function templateDetail(string $name, float $quantity, float $total, array $attributes = []): PenjualanTransactionDetailModel
    {
        return (new PenjualanTransactionDetailModel)->forceFill(array_merge([
            'nama_obat' => $name,
            'satuan_jual' => '[Satuan]',
            'qty_jual' => $quantity,
            'harga_jual' => $total / max($quantity, 1),
            'subtotal_gross' => $total,
            'diskon_nominal' => 0,
            'total_line' => $total,
        ], $attributes));
    }
}
