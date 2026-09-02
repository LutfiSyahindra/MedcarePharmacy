<?php

namespace App\Services\Menu\AnalisisProfitabilitas;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProfitabilityAnalysisService
{
    public const SORTS = [
        'gross_profit',
        'gross_margin',
        'gmroi',
        'net_sales',
        'net_hpp',
        'average_inventory_cost',
        'name',
    ];

    public function __construct(private readonly ProfitabilityMetricsService $metrics) {}

    public function build(User $user, array $filters): array
    {
        $context = $this->context($user, $filters['branch_id'] ?? null);
        $investment = $this->metrics->inventoryInvestment($context['branch_ids'], $filters['start'], $filters['end']);
        $productRows = $this->metrics->productRows($context['branch_ids'], $filters['start'], $filters['end']);
        $measuredMedicineIds = $productRows->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique();
        $inventoryOnlyRows = MasterObatModel::query()
            ->with('kategori:id,name')
            ->whereIn('id', $investment->keys()->diff($measuredMedicineIds)->all())
            ->get(['id', 'kode_obat', 'nama_obat', 'category_id'])
            ->map(fn (MasterObatModel $medicine) => [
                'key' => 'id:'.(int) $medicine->id,
                'id' => (int) $medicine->id,
                'code' => $medicine->kode_obat ?: '-',
                'name' => $medicine->nama_obat ?: 'Produk tanpa nama',
                'category' => $medicine->kategori?->name ?: 'Tanpa kategori',
                'transactions' => 0,
                'sold_qty' => 0.0,
                'return_qty' => 0.0,
                'net_qty' => 0.0,
                'sales_value' => 0.0,
                'returns_value' => 0.0,
                'net_sales' => 0.0,
                'sales_hpp' => 0.0,
                'return_hpp' => 0.0,
                'net_hpp' => 0.0,
                'gross_profit' => 0.0,
                'gross_margin' => null,
            ]);
        $rows = $productRows
            ->concat($inventoryOnlyRows)
            ->map(function (array $row) use ($investment, $filters) {
                $inventory = $row['id'] ? $investment->get($row['id'], []) : [];
                $averageInventory = (float) ($inventory['average_inventory_cost'] ?? 0);
                $gmroi = $averageInventory > 0 ? $row['gross_profit'] / $averageInventory : null;

                return [
                    ...$row,
                    'opening_inventory_cost' => (float) ($inventory['opening_inventory_cost'] ?? 0),
                    'closing_inventory_cost' => (float) ($inventory['closing_inventory_cost'] ?? 0),
                    'average_inventory_cost' => $averageInventory,
                    'gmroi' => $gmroi === null ? null : round($gmroi, 4),
                    'gmroi_percent' => $gmroi === null ? null : round($gmroi * 100, 2),
                    'period_days' => (int) $filters['period_days'],
                ];
            });

        if ($filters['search'] !== '') {
            $search = mb_strtolower($filters['search']);
            $rows = $rows->filter(fn (array $row) => str_contains(
                mb_strtolower(implode(' ', [$row['code'], $row['name'], $row['category']])),
                $search,
            ));
        }

        $rows = $this->withRanks($rows->values());
        $summary = $this->summary($rows);
        $topProfit = $rows->sortByDesc('gross_profit')->take(10)->values();
        $marginLeaders = $rows
            ->filter(fn (array $row) => $row['gross_margin'] !== null && $row['net_sales'] > 0)
            ->sortByDesc('gross_margin')
            ->take(10)
            ->values();
        $gmroiLeaders = $rows
            ->whereNotNull('gmroi')
            ->sortByDesc('gmroi')
            ->take(10)
            ->values();
        $sorted = $this->sortRows($rows, $filters['sort'], $filters['direction']);
        $total = $sorted->count();

        if ($filters['top'] !== 'all') {
            $sorted = $sorted->take((int) $filters['top'])->values();
        }

        return [
            'meta' => [
                'branch_label' => $context['branch_label'],
                'selected_branch_id' => $context['selected_branch_id'],
                'branches' => $context['branches']->map(fn (BranchModel $branch) => [
                    'id' => (int) $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name,
                ])->values()->all(),
                'date_start' => $filters['start']->toDateString(),
                'date_end' => $filters['end']->toDateString(),
                'period_days' => (int) $filters['period_days'],
                'sort' => $filters['sort'],
                'direction' => $filters['direction'],
                'top' => $filters['top'],
                'total_products' => $total,
                'displayed_products' => $sorted->count(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'revenue_basis' => 'Penjualan completed dikurangi retur posted pada periode aktif; diskon transaksi dialokasikan proporsional ke produk.',
                'inventory_basis' => 'Rata-rata investasi persediaan = (nilai stok awal + nilai stok akhir) / 2 berdasarkan saldo batch dan harga beli pada kartu stok.',
            ],
            'summary' => $summary,
            'rows' => $sorted->all(),
            'top_profit_products' => $topProfit->all(),
            'margin_leaders' => $marginLeaders->all(),
            'gmroi_leaders' => $gmroiLeaders->all(),
        ];
    }

    private function context(User $user, ?int $selectedBranchId): array
    {
        $allowedIds = BranchAccess::userBranchIds($user);
        $branches = BranchModel::query()
            ->whereIn('id', $allowedIds)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        if ($selectedBranchId && ! in_array($selectedBranchId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang tidak tersedia untuk akun ini.',
            ]);
        }

        return [
            'branches' => $branches,
            'branch_ids' => $selectedBranchId ? [$selectedBranchId] : $allowedIds,
            'selected_branch_id' => $selectedBranchId,
            'branch_label' => $selectedBranchId
                ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
                : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang')),
        ];
    }

    private function withRanks(Collection $rows): Collection
    {
        $profitRanks = $rows->sortByDesc('gross_profit')->values()->pluck('key')->flip();
        $marginRanks = $rows->whereNotNull('gross_margin')->sortByDesc('gross_margin')->values()->pluck('key')->flip();
        $gmroiRanks = $rows->whereNotNull('gmroi')->sortByDesc('gmroi')->values()->pluck('key')->flip();

        return $rows->map(fn (array $row) => [
            ...$row,
            'profit_rank' => $profitRanks->has($row['key']) ? (int) $profitRanks->get($row['key']) + 1 : null,
            'margin_rank' => $marginRanks->has($row['key']) ? (int) $marginRanks->get($row['key']) + 1 : null,
            'gmroi_rank' => $gmroiRanks->has($row['key']) ? (int) $gmroiRanks->get($row['key']) + 1 : null,
        ]);
    }

    private function summary(Collection $rows): array
    {
        $netSales = round((float) $rows->sum('net_sales'), 2);
        $netHpp = round((float) $rows->sum('net_hpp'), 2);
        $grossProfit = round((float) $rows->sum('gross_profit'), 2);
        $averageInventory = round((float) $rows->sum('average_inventory_cost'), 2);

        return [
            'net_sales' => $netSales,
            'net_hpp' => $netHpp,
            'gross_profit' => $grossProfit,
            'gross_margin' => $netSales != 0.0 ? round(($grossProfit / $netSales) * 100, 2) : null,
            'average_inventory_cost' => $averageInventory,
            'gmroi' => $averageInventory > 0 ? round($grossProfit / $averageInventory, 4) : null,
            'gmroi_percent' => $averageInventory > 0 ? round(($grossProfit / $averageInventory) * 100, 2) : null,
            'product_count' => $rows->count(),
            'profitable_products' => $rows->where('gross_profit', '>', 0)->count(),
            'loss_products' => $rows->where('gross_profit', '<', 0)->count(),
            'unmeasured_gmroi_products' => $rows->whereNull('gmroi')->count(),
            'top_profit_product' => $rows->sortByDesc('gross_profit')->first()['name'] ?? null,
        ];
    }

    private function sortRows(Collection $rows, string $sort, string $direction): Collection
    {
        if (! in_array($sort, self::SORTS, true)) {
            $sort = 'gross_profit';
        }

        $sorted = $direction === 'asc'
            ? $rows->sortBy(fn (array $row) => $row[$sort] ?? PHP_FLOAT_MAX, SORT_NATURAL | SORT_FLAG_CASE)
            : $rows->sortByDesc(fn (array $row) => $row[$sort] ?? -PHP_FLOAT_MAX, SORT_NATURAL | SORT_FLAG_CASE);

        return $sorted->values();
    }
}
