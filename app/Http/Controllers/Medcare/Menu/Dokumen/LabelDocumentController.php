<?php

namespace App\Http\Controllers\Medcare\Menu\Dokumen;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Services\Menu\Penjualan\PenjualanPosService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LabelDocumentController extends Controller
{
    private const LABEL_TYPES = [
        'penjualan_resep',
        'penjualan_racikan',
    ];

    public function __construct(private readonly PenjualanPosService $posService) {}

    /**
     * @return array<int, string>
     */
    public static function templateTypes(): array
    {
        return [
            'non-racikan',
            'racikan',
        ];
    }

    public function index(Request $request): View
    {
        $branches = $this->accessibleBranches();
        $requestedBranchId = $request->integer('branch_id');
        $activeBranchId = (int) $request->session()->get('active_branch_id', 0);
        $selectedBranch = $branches->firstWhere('id', $requestedBranchId)
            ?: $branches->firstWhere('id', $activeBranchId)
            ?: $branches->first();

        return view('medcare.menu.dokumen.etiket', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
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

        abort_if(! $branch, 422, 'User belum memiliki akses cabang untuk membuat template etiket.');

        return view('medcare.menu.dokumen.template-etiket', [
            'branch' => $branch,
            'type' => $type,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function table(Request $request): JsonResponse
    {
        $branchIds = $this->posService->transactionBranchIds();
        $filters = $request->validate([
            'etiket_search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(self::LABEL_TYPES)],
            'branch_id' => ['nullable', 'integer', Rule::in($branchIds)],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_start'],
        ]);

        $summaryQuery = $this->filteredQuery($branchIds, $filters);
        $summaryRows = (clone $summaryQuery)
            ->with('details:id,penjualan_transaction_id,racikan_group')
            ->get(['id', 'jenis_transaksi']);
        $summary = $this->summary($summaryRows);

        $query = $summaryQuery
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('jenis_transaksi', $type))
            ->with([
                'branch:id,name',
                'createdBy:id,name',
                'details:id,penjualan_transaction_id,nama_obat,aturan_pakai,racikan_group',
            ])
            ->latest('tanggal_transaksi')
            ->latest('id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('tanggal_transaksi', fn (PenjualanTransactionModel $transaction) => $transaction->tanggal_transaksi?->format('Y-m-d H:i'))
            ->editColumn('customer_name', fn (PenjualanTransactionModel $transaction) => $transaction->customer_name ?: 'Umum')
            ->addColumn('type_label', fn (PenjualanTransactionModel $transaction) => $transaction->jenis_transaksi === 'penjualan_racikan'
                ? 'Etiket Racikan'
                : 'Etiket Non Racikan')
            ->addColumn('type_short_label', fn (PenjualanTransactionModel $transaction) => $transaction->jenis_transaksi === 'penjualan_racikan'
                ? 'Racikan'
                : 'Non Racikan')
            ->addColumn('branch_name', fn (PenjualanTransactionModel $transaction) => $transaction->branch?->name ?: '-')
            ->addColumn('cashier_name', fn (PenjualanTransactionModel $transaction) => $transaction->createdBy?->name ?: '-')
            ->addColumn('label_count', fn (PenjualanTransactionModel $transaction) => $this->labelCount($transaction))
            ->addColumn('item_count', fn (PenjualanTransactionModel $transaction) => $transaction->details->count())
            ->addColumn('medicine_names', fn (PenjualanTransactionModel $transaction) => $transaction->details
                ->pluck('nama_obat')
                ->filter()
                ->unique()
                ->values()
                ->implode(', '))
            ->addColumn('preview_url', fn (PenjualanTransactionModel $transaction) => route('penjualan.pos.labels', [
                'id' => $transaction->id,
                'autoprint' => 0,
            ]))
            ->addColumn('print_url', fn (PenjualanTransactionModel $transaction) => route('penjualan.pos.labels', [
                'id' => $transaction->id,
                'autoprint' => 1,
            ]))
            ->rawColumns([])
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
            ->whereIn('jenis_transaksi', self::LABEL_TYPES)
            ->when($filters['branch_id'] ?? null, fn (Builder $query, int $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['date_start'] ?? null, fn (Builder $query, string $date) => $query->whereDate('tanggal_transaksi', '>=', $date))
            ->when($filters['date_end'] ?? null, fn (Builder $query, string $date) => $query->whereDate('tanggal_transaksi', '<=', $date));

        $search = trim((string) ($filters['etiket_search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $like = '%'.$search.'%';
                $query->where('nomor_transaksi', 'like', $like)
                    ->orWhere('nomor_resep', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('dokter_name', 'like', $like)
                    ->orWhereHas('branch', fn (Builder $query) => $query->where('name', 'like', $like))
                    ->orWhereHas('details', fn (Builder $query) => $query
                        ->where('nama_obat', 'like', $like)
                        ->orWhere('aturan_pakai', 'like', $like));
            });
        }

        return $query;
    }

    /**
     * @param  Collection<int, PenjualanTransactionModel>  $transactions
     * @return array<string, int>
     */
    private function summary(Collection $transactions): array
    {
        $nonCompound = $transactions->where('jenis_transaksi', 'penjualan_resep');
        $compound = $transactions->where('jenis_transaksi', 'penjualan_racikan');

        return [
            'sets' => $transactions->count(),
            'labels' => (int) $transactions->sum(fn (PenjualanTransactionModel $transaction) => $this->labelCount($transaction)),
            'non_compound' => (int) $nonCompound->sum(fn (PenjualanTransactionModel $transaction) => $this->labelCount($transaction)),
            'compound' => (int) $compound->sum(fn (PenjualanTransactionModel $transaction) => $this->labelCount($transaction)),
        ];
    }

    private function labelCount(PenjualanTransactionModel $transaction): int
    {
        if ($transaction->jenis_transaksi !== 'penjualan_racikan') {
            return $transaction->details->count();
        }

        return $transaction->details
            ->map(fn ($detail) => trim((string) $detail->racikan_group) ?: 'R/ -')
            ->unique()
            ->count();
    }

    private function accessibleBranches(): Collection
    {
        return BranchModel::query()
            ->with('apotekProfile')
            ->whereIn('id', $this->posService->transactionBranchIds())
            ->orderBy('name')
            ->get();
    }
}
