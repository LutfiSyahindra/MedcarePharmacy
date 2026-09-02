<?php

namespace App\Services\Menu\AnalisisOmzet;

use App\Models\BranchModel;
use App\Models\Menu\Analisis\OmzetTargetModel;
use App\Models\User;
use App\Services\Menu\Penjualan\PenjualanPosService;
use App\Support\BranchAccess;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RevenueAnalysisService
{
    private const DAY_NAMES = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    private const MONTH_NAMES = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function build(User $user, array $filters, bool $withOptions = true): array
    {
        $context = $this->context($user, $filters['branch_id'] ?? null);
        $branchIds = $context['branch_ids'];
        [$previousStart, $previousEnd] = $this->previousPeriod($filters['start'], $filters['end']);

        $current = $this->periodMetrics($branchIds, $filters, $filters['start'], $filters['end']);
        $previous = $this->periodMetrics($branchIds, $filters, $previousStart, $previousEnd);
        $growth = $this->growth($current['net_revenue'], $previous['net_revenue']);
        $summary = [
            ...$current,
            'previous_net_revenue' => $previous['net_revenue'],
            'growth_percent' => $growth,
        ];

        $trend = $this->trend($branchIds, $filters, $previousStart, $previousEnd);
        $products = $this->products($branchIds, $filters, $previousStart, $previousEnd, $summary['net_revenue']);
        $fastMoving = $this->fastMoving($branchIds, $filters, $previousStart, $previousEnd, $summary['transactions']);
        $marketBasket = $this->marketBasket($branchIds, $filters);
        $categories = $this->categories($branchIds, $filters, $summary['net_revenue']);
        $saleTypes = $this->saleTypes($branchIds, $filters, $summary['net_revenue']);
        $payments = $this->payments($branchIds, $filters, $summary['net_revenue']);
        $hourly = $this->hourly($branchIds, $filters);
        $weekdays = $this->weekdays($branchIds, $filters);
        $cashiers = $this->cashiers($branchIds, $filters, $summary['net_revenue']);
        $target = $this->target($branchIds, $filters, $summary['net_revenue']);

        return [
            'meta' => [
                'date_start' => $filters['start']->toDateString(),
                'date_end' => $filters['end']->toDateString(),
                'previous_date_start' => $previousStart->toDateString(),
                'previous_date_end' => $previousEnd->toDateString(),
                'period_label' => $this->dateLabel($filters['start']).' - '.$this->dateLabel($filters['end']),
                'previous_period_label' => $this->dateLabel($previousStart).' - '.$this->dateLabel($previousEnd),
                'branch_label' => $context['branch_label'],
                'selected_branch_id' => $context['selected_branch_id'],
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'granularity' => $filters['granularity'],
                'top' => $filters['top'],
                'product_metric' => $filters['product_metric'] ?? 'revenue',
                'active_filters' => $this->activeFilterLabels($filters, $context),
            ],
            'summary' => $summary,
            'trend' => $trend,
            'products' => $products,
            'fast_moving' => $fastMoving,
            'market_basket' => $marketBasket,
            'categories' => $categories,
            'sale_types' => $saleTypes,
            'payments' => $payments,
            'hourly' => $hourly,
            'weekdays' => $weekdays,
            'cashiers' => $cashiers,
            'target' => $target,
            'insights' => $this->insights($summary, $products, $categories, $payments, $hourly, $weekdays, $marketBasket),
            'options' => $withOptions ? $this->options($branchIds, $context['branches']) : [],
        ];
    }

    public function saveTarget(User $user, int $branchId, Carbon $start, Carbon $end, float $amount): OmzetTargetModel
    {
        $this->context($user, $branchId);

        return OmzetTargetModel::query()->updateOrCreate(
            [
                'branch_id' => $branchId,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
            [
                'target_amount' => round(max(0, $amount), 2),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        );
    }

    private function context(User $user, ?int $selectedBranchId): array
    {
        $allowedIds = BranchAccess::userBranchIds($user);
        $branches = BranchModel::query()
            ->whereIn('id', $allowedIds === [] ? [-1] : $allowedIds)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        if ($selectedBranchId !== null && ! in_array($selectedBranchId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang tidak tersedia untuk akun ini.',
            ]);
        }

        return [
            'branches' => $branches,
            'branch_ids' => $selectedBranchId !== null ? [$selectedBranchId] : $allowedIds,
            'selected_branch_id' => $selectedBranchId,
            'branch_label' => $selectedBranchId !== null
                ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
                : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang')),
        ];
    }

    private function previousPeriod(Carbon $start, Carbon $end): array
    {
        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        return [$previousStart, $previousEnd];
    }

    private function periodMetrics(array $branchIds, array $filters, Carbon $start, Carbon $end): array
    {
        $sales = $this->saleLines($branchIds, $filters, $start, $end)
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM('.$this->saleGrossExpression().'), 0) as gross_revenue')
            ->selectRaw('COALESCE(SUM('.$this->saleDiscountExpression().'), 0) as discount')
            ->selectRaw('COALESCE(SUM('.$this->saleRevenueExpression().'), 0) as revenue')
            ->first();
        $returns = $this->returnLines($branchIds, $filters, $start, $end)
            ->selectRaw('COUNT(DISTINCT returns.id) as documents')
            ->selectRaw('COALESCE(SUM(return_details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM('.$this->returnRevenueExpression().'), 0) as revenue')
            ->first();

        $revenue = round((float) ($sales->revenue ?? 0), 2);
        $returnValue = round((float) ($returns->revenue ?? 0), 2);
        $transactions = (int) ($sales->transactions ?? 0);

        return [
            'total_revenue' => $revenue,
            'net_revenue' => round($revenue - $returnValue, 2),
            'transactions' => $transactions,
            'qty_sold' => round((float) ($sales->qty ?? 0), 2),
            'average_transaction' => $transactions > 0 ? round($revenue / $transactions, 2) : 0,
            'total_discount' => round((float) ($sales->discount ?? 0), 2),
            'gross_revenue' => round((float) ($sales->gross_revenue ?? 0), 2),
            'return_value' => $returnValue,
            'return_qty' => round((float) ($returns->qty ?? 0), 2),
            'return_documents' => (int) ($returns->documents ?? 0),
        ];
    }

    private function trend(array $branchIds, array $filters, Carbon $previousStart, Carbon $previousEnd): array
    {
        $currentDaily = $this->dailySeries($branchIds, $filters, $filters['start'], $filters['end']);
        $previousDaily = $this->dailySeries($branchIds, $filters, $previousStart, $previousEnd);
        $currentBuckets = $this->aggregateDailySeries($currentDaily, $filters['start'], $filters['end'], $filters['granularity']);
        $previousBuckets = $this->aggregateDailySeries($previousDaily, $previousStart, $previousEnd, $filters['granularity']);

        return [
            'granularity' => $filters['granularity'],
            'labels' => $currentBuckets->pluck('label')->all(),
            'previous_labels' => $previousBuckets->pluck('label')->all(),
            'current_revenue' => $currentBuckets->pluck('net_revenue')->all(),
            'previous_revenue' => $currentBuckets->keys()->map(
                fn (int $index) => (float) ($previousBuckets->get($index)['net_revenue'] ?? 0)
            )->all(),
            'transactions' => $currentBuckets->pluck('transactions')->all(),
            'returns' => $currentBuckets->pluck('returns')->all(),
        ];
    }

    private function dailySeries(array $branchIds, array $filters, Carbon $start, Carbon $end): Collection
    {
        $saleDate = $this->dateExpression('sales.tanggal_transaksi');
        $returnDate = $this->dateExpression('returns.tanggal_retur');
        $sales = $this->saleLines($branchIds, $filters, $start, $end)
            ->selectRaw("{$saleDate} as event_date")
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COALESCE(SUM('.$this->saleRevenueExpression().'), 0) as revenue')
            ->groupByRaw($saleDate)
            ->get()
            ->keyBy('event_date');
        $returns = $this->returnLines($branchIds, $filters, $start, $end)
            ->selectRaw("{$returnDate} as event_date")
            ->selectRaw('COALESCE(SUM('.$this->returnRevenueExpression().'), 0) as revenue')
            ->groupByRaw($returnDate)
            ->get()
            ->keyBy('event_date');

        $rows = collect();
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end->copy()->startOfDay())) {
            $date = $cursor->toDateString();
            $saleRevenue = (float) ($sales->get($date)?->revenue ?? 0);
            $returnRevenue = (float) ($returns->get($date)?->revenue ?? 0);
            $rows->put($date, [
                'date' => $date,
                'revenue' => round($saleRevenue, 2),
                'returns' => round($returnRevenue, 2),
                'net_revenue' => round($saleRevenue - $returnRevenue, 2),
                'transactions' => (int) ($sales->get($date)?->transactions ?? 0),
            ]);
            $cursor->addDay();
        }

        return $rows;
    }

    private function aggregateDailySeries(Collection $daily, Carbon $start, Carbon $end, string $granularity): Collection
    {
        return $daily->groupBy(fn (array $row) => $this->bucketKey(Carbon::parse($row['date']), $granularity))
            ->map(function (Collection $rows, string $key) use ($granularity) {
                return [
                    'key' => $key,
                    'label' => $this->bucketLabel($key, $granularity),
                    'revenue' => round((float) $rows->sum('revenue'), 2),
                    'returns' => round((float) $rows->sum('returns'), 2),
                    'net_revenue' => round((float) $rows->sum('net_revenue'), 2),
                    'transactions' => (int) $rows->sum('transactions'),
                ];
            })
            ->values();
    }

    private function products(array $branchIds, array $filters, Carbon $previousStart, Carbon $previousEnd, float $netRevenue): array
    {
        $current = $this->productRows($branchIds, $filters, $filters['start'], $filters['end']);
        $previous = $this->productRows($branchIds, $filters, $previousStart, $previousEnd)
            ->keyBy('key');
        $rows = $current->map(function (array $row) use ($previous, $netRevenue) {
            $previousRevenue = (float) ($previous->get($row['key'])['revenue'] ?? 0);
            $row['contribution_percent'] = $netRevenue != 0.0 ? round(($row['revenue'] / $netRevenue) * 100, 2) : 0;
            $row['previous_revenue'] = $previousRevenue;
            $row['growth_percent'] = $this->growth($row['revenue'], $previousRevenue);
            $row['net_qty'] = round($row['qty'] - $row['return_qty'], 2);

            return $row;
        })->sortByDesc(match ($filters['product_metric'] ?? 'revenue') {
            'qty' => 'net_qty',
            'transactions' => 'transactions',
            default => 'revenue',
        })->values();

        if ($filters['top'] !== 'all') {
            $rows = $rows->take((int) $filters['top'])->values();
        }

        return $rows->all();
    }

    private function fastMoving(array $branchIds, array $filters, Carbon $previousStart, Carbon $previousEnd, int $totalTransactions): array
    {
        $periodDays = (int) max(1, $filters['start']->copy()->startOfDay()->diffInDays($filters['end']->copy()->startOfDay()) + 1);
        $previous = $this->productRows($branchIds, $filters, $previousStart, $previousEnd)->keyBy('key');
        $rows = $this->productRows($branchIds, $filters, $filters['start'], $filters['end'])
            ->map(function (array $row) use ($periodDays, $previous, $totalTransactions) {
                $netQty = round($row['qty'] - $row['return_qty'], 2);
                $previousRow = $previous->get($row['key']);
                $previousNetQty = $previousRow
                    ? round($previousRow['qty'] - $previousRow['return_qty'], 2)
                    : 0.0;

                return [
                    ...$row,
                    'net_qty' => $netQty,
                    'previous_net_qty' => $previousNetQty,
                    'average_daily_qty' => round($netQty / $periodDays, 2),
                    'sales_day_frequency_percent' => round(($row['sales_days'] / $periodDays) * 100, 2),
                    'transaction_penetration_percent' => $totalTransactions > 0
                        ? round(($row['transactions'] / $totalTransactions) * 100, 2)
                        : 0,
                    'qty_growth_percent' => $this->growth($netQty, $previousNetQty),
                ];
            })
            ->filter(fn (array $row) => $row['net_qty'] > 0)
            ->sort(function (array $left, array $right) {
                $velocity = $right['average_daily_qty'] <=> $left['average_daily_qty'];

                return $velocity !== 0 ? $velocity : ($right['transactions'] <=> $left['transactions']);
            })
            ->values()
            ->map(fn (array $row, int $index) => ['rank' => $index + 1, ...$row]);

        if ($filters['top'] !== 'all') {
            $rows = $rows->take((int) $filters['top'])->values();
        }

        $leader = $rows->first();

        return [
            'summary' => [
                'products' => $rows->count(),
                'net_qty' => round((float) $rows->sum('net_qty'), 2),
                'leader' => $leader['name'] ?? null,
                'leader_daily_qty' => (float) ($leader['average_daily_qty'] ?? 0),
                'period_days' => $periodDays,
            ],
            'rows' => $rows->all(),
        ];
    }

    private function productRows(array $branchIds, array $filters, Carbon $start, Carbon $end): Collection
    {
        $sales = $this->saleLines($branchIds, $filters, $start, $end)
            ->select('details.obat_id', 'details.kode_obat', 'details.nama_obat')
            ->selectRaw("COALESCE(categories.name, 'Tanpa kategori') as category")
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COUNT(DISTINCT '.$this->dateExpression('sales.tanggal_transaksi').') as sales_days')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM('.$this->saleRevenueExpression().'), 0) as revenue')
            ->groupBy('details.obat_id', 'details.kode_obat', 'details.nama_obat', 'categories.name')
            ->get();
        $returns = $this->returnLines($branchIds, $filters, $start, $end)
            ->select('return_details.obat_id', 'return_details.kode_obat', 'return_details.nama_obat')
            ->selectRaw('COALESCE(SUM(return_details.qty_jual), 0) as return_qty')
            ->selectRaw('COALESCE(SUM('.$this->returnRevenueExpression().'), 0) as return_value')
            ->groupBy('return_details.obat_id', 'return_details.kode_obat', 'return_details.nama_obat')
            ->get()
            ->keyBy(fn ($row) => $this->productKey($row->obat_id, $row->kode_obat, $row->nama_obat));

        $rows = $sales->map(function ($row) use ($returns) {
            $key = $this->productKey($row->obat_id, $row->kode_obat, $row->nama_obat);
            $return = $returns->get($key);

            return [
                'key' => $key,
                'id' => $row->obat_id === null ? null : (int) $row->obat_id,
                'code' => $row->kode_obat ?: '-',
                'name' => $row->nama_obat ?: 'Produk tanpa nama',
                'category' => $row->category,
                'qty' => round((float) $row->qty, 2),
                'return_qty' => round((float) ($return->return_qty ?? 0), 2),
                'transactions' => (int) $row->transactions,
                'sales_days' => (int) $row->sales_days,
                'revenue' => round((float) $row->revenue - (float) ($return->return_value ?? 0), 2),
                'return_value' => round((float) ($return->return_value ?? 0), 2),
            ];
        })->keyBy('key');

        foreach ($returns as $key => $return) {
            if ($rows->has($key)) {
                continue;
            }
            $rows->put($key, [
                'key' => $key,
                'id' => $return->obat_id === null ? null : (int) $return->obat_id,
                'code' => $return->kode_obat ?: '-',
                'name' => $return->nama_obat ?: 'Produk tanpa nama',
                'category' => 'Tanpa kategori',
                'qty' => 0,
                'return_qty' => round((float) $return->return_qty, 2),
                'transactions' => 0,
                'sales_days' => 0,
                'revenue' => round(-(float) $return->return_value, 2),
                'return_value' => round((float) $return->return_value, 2),
            ]);
        }

        return $rows->values();
    }

    private function marketBasket(array $branchIds, array $filters): array
    {
        $totalTransactions = $this->basketTransactions($branchIds, $filters)->count('sales.id');
        if ($totalTransactions === 0) {
            return $this->emptyMarketBasket();
        }

        $productTransactions = $this->basketTransactions($branchIds, $filters)
            ->join('penjualan_transaction_details as basket_details', 'basket_details.penjualan_transaction_id', '=', 'sales.id')
            ->join('master_obats as basket_medicines', 'basket_medicines.id', '=', 'basket_details.obat_id')
            ->when($filters['category_id'] ?? null, fn (Builder $query, int $id) => $query->where('basket_medicines.category_id', $id))
            ->when($filters['golongan_id'] ?? null, fn (Builder $query, int $id) => $query->where('basket_medicines.golongan_id', $id))
            ->select('basket_details.obat_id')
            ->selectRaw('COUNT(DISTINCT sales.id) as transaction_count')
            ->groupBy('basket_details.obat_id')
            ->pluck('transaction_count', 'basket_details.obat_id');

        $pairQuery = $this->basketTransactions($branchIds, $filters)
            ->join('penjualan_transaction_details as basket_a', 'basket_a.penjualan_transaction_id', '=', 'sales.id')
            ->join('penjualan_transaction_details as basket_b', function ($join) {
                $join->on('basket_b.penjualan_transaction_id', '=', 'basket_a.penjualan_transaction_id')
                    ->on('basket_b.obat_id', '>', 'basket_a.obat_id');
            })
            ->join('master_obats as medicine_a', 'medicine_a.id', '=', 'basket_a.obat_id')
            ->join('master_obats as medicine_b', 'medicine_b.id', '=', 'basket_b.obat_id')
            ->when($filters['category_id'] ?? null, function (Builder $query, int $id) {
                $query->where('medicine_a.category_id', $id)->where('medicine_b.category_id', $id);
            })
            ->when($filters['golongan_id'] ?? null, function (Builder $query, int $id) {
                $query->where('medicine_a.golongan_id', $id)->where('medicine_b.golongan_id', $id);
            })
            ->when($filters['medicine_id'] ?? null, function (Builder $query, int $id) {
                $query->where(fn (Builder $pair) => $pair
                    ->where('basket_a.obat_id', $id)
                    ->orWhere('basket_b.obat_id', $id));
            })
            ->select('basket_a.obat_id as product_a_id', 'medicine_a.kode_obat as product_a_code', 'medicine_a.nama_obat as product_a_name')
            ->addSelect('basket_b.obat_id as product_b_id', 'medicine_b.kode_obat as product_b_code', 'medicine_b.nama_obat as product_b_name')
            ->selectRaw('COUNT(DISTINCT sales.id) as pair_transactions')
            ->groupBy(
                'basket_a.obat_id',
                'medicine_a.kode_obat',
                'medicine_a.nama_obat',
                'basket_b.obat_id',
                'medicine_b.kode_obat',
                'medicine_b.nama_obat',
            )
            ->orderByDesc('pair_transactions');

        $maximumRows = $filters['top'] === 'all' ? 200 : (int) $filters['top'];
        $pairs = $pairQuery->limit($maximumRows)->get();
        $rows = $pairs->map(function ($pair) use ($productTransactions, $totalTransactions) {
            $pairTransactions = (int) $pair->pair_transactions;
            $productATransactions = (int) ($productTransactions[$pair->product_a_id] ?? 0);
            $productBTransactions = (int) ($productTransactions[$pair->product_b_id] ?? 0);
            $lift = $productATransactions > 0 && $productBTransactions > 0
                ? ($pairTransactions * $totalTransactions) / ($productATransactions * $productBTransactions)
                : 0;
            $roundedLift = round($lift, 2);

            return [
                'product_a' => [
                    'id' => (int) $pair->product_a_id,
                    'code' => $pair->product_a_code,
                    'name' => $pair->product_a_name,
                ],
                'product_b' => [
                    'id' => (int) $pair->product_b_id,
                    'code' => $pair->product_b_code,
                    'name' => $pair->product_b_name,
                ],
                'pair_transactions' => $pairTransactions,
                'support_percent' => round(($pairTransactions / $totalTransactions) * 100, 2),
                'confidence_a_to_b_percent' => $productATransactions > 0
                    ? round(($pairTransactions / $productATransactions) * 100, 2)
                    : 0,
                'confidence_b_to_a_percent' => $productBTransactions > 0
                    ? round(($pairTransactions / $productBTransactions) * 100, 2)
                    : 0,
                'lift' => $roundedLift,
                'strength' => $roundedLift > 1.2 ? 'Kuat' : ($roundedLift > 1 ? 'Positif' : ($roundedLift === 1.0 ? 'Netral' : 'Lemah')),
            ];
        })->values();
        $leadingPair = $rows->first();

        return [
            'summary' => [
                'transactions_analyzed' => $totalTransactions,
                'pairs_found' => $rows->count(),
                'leading_pair' => $leadingPair
                    ? $leadingPair['product_a']['name'].' + '.$leadingPair['product_b']['name']
                    : null,
                'leading_lift' => (float) ($leadingPair['lift'] ?? 0),
                'row_limit' => $maximumRows,
            ],
            'rows' => $rows->all(),
        ];
    }

    private function basketTransactions(array $branchIds, array $filters): Builder
    {
        return DB::table('penjualan_transactions as sales')
            ->whereIn('sales.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$filters['start']->copy()->startOfDay(), $filters['end']->copy()->endOfDay()])
            ->when($filters['cashier_id'] ?? null, fn (Builder $query, int $id) => $query->where('sales.completed_by', $id))
            ->when($filters['shift_id'] ?? null, fn (Builder $query, int $id) => $query->where('sales.cashier_shift_id', $id))
            ->when($filters['transaction_type'] ?? null, fn (Builder $query, string $type) => $query->where('sales.jenis_transaksi', $type))
            ->when($filters['payment_method'] ?? null, function (Builder $query, string $method) {
                $query->whereExists(function (Builder $payments) use ($method) {
                    $payments->selectRaw('1')
                        ->from('penjualan_payments as basket_payments')
                        ->whereColumn('basket_payments.penjualan_transaction_id', 'sales.id')
                        ->where('basket_payments.metode', $method);
                });
            });
    }

    private function emptyMarketBasket(): array
    {
        return [
            'summary' => [
                'transactions_analyzed' => 0,
                'pairs_found' => 0,
                'leading_pair' => null,
                'leading_lift' => 0,
                'row_limit' => 0,
            ],
            'rows' => [],
        ];
    }

    private function categories(array $branchIds, array $filters, float $netRevenue): array
    {
        $sales = $this->saleLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->selectRaw("COALESCE(categories.name, 'Tanpa kategori') as label")
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM('.$this->saleRevenueExpression().'), 0) as revenue')
            ->groupBy('categories.name')->get()->keyBy('label');
        $returns = $this->returnLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->selectRaw("COALESCE(categories.name, 'Tanpa kategori') as label")
            ->selectRaw('COALESCE(SUM(return_details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM('.$this->returnRevenueExpression().'), 0) as revenue')
            ->groupBy('categories.name')->get()->keyBy('label');

        return $this->mergeDimensionRows($sales, $returns, $netRevenue, true);
    }

    private function saleTypes(array $branchIds, array $filters, float $netRevenue): array
    {
        $sales = $this->saleLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->select('sales.jenis_transaksi as key')
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COALESCE(SUM('.$this->saleRevenueExpression().'), 0) as revenue')
            ->groupBy('sales.jenis_transaksi')->get()->keyBy('key');
        $returns = $this->returnLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->select('sales.jenis_transaksi as key')
            ->selectRaw('COALESCE(SUM('.$this->returnRevenueExpression().'), 0) as revenue')
            ->groupBy('sales.jenis_transaksi')->get()->keyBy('key');
        $keys = $sales->keys()->merge($returns->keys())->unique();

        return $keys->map(function ($key) use ($sales, $returns, $netRevenue) {
            $revenue = round((float) ($sales->get($key)?->revenue ?? 0) - (float) ($returns->get($key)?->revenue ?? 0), 2);

            return [
                'key' => $key,
                'label' => PenjualanPosService::TRANSACTION_TYPES[$key] ?? str($key)->replace('_', ' ')->title()->toString(),
                'transactions' => (int) ($sales->get($key)?->transactions ?? 0),
                'revenue' => $revenue,
                'contribution_percent' => $netRevenue != 0.0 ? round(($revenue / $netRevenue) * 100, 2) : 0,
            ];
        })->sortByDesc('revenue')->values()->all();
    }

    private function payments(array $branchIds, array $filters, float $netRevenue): array
    {
        $filtersWithoutPayment = [...$filters, 'payment_method' => null];
        $paymentTotals = $this->paymentTotalsSubquery();
        $saleRevenue = $this->saleRevenueExpression();
        $returnRevenue = $this->returnRevenueExpression();
        $sales = $this->saleLines($branchIds, $filtersWithoutPayment, $filters['start'], $filters['end'])
            ->join('penjualan_payments as payment_breakdown', 'payment_breakdown.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoinSub($paymentTotals, 'payment_breakdown_totals', 'payment_breakdown_totals.penjualan_transaction_id', '=', 'sales.id')
            ->when($filters['payment_method'], fn (Builder $query, string $method) => $query->where('payment_breakdown.metode', $method))
            ->select('payment_breakdown.metode as key')
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw("COALESCE(SUM({$saleRevenue} * CASE WHEN COALESCE(payment_breakdown_totals.amount, 0) > 0 THEN (1.0 * payment_breakdown.amount) / payment_breakdown_totals.amount ELSE 1 END), 0) as revenue")
            ->groupBy('payment_breakdown.metode')->get()->keyBy('key');
        $returns = $this->returnLines($branchIds, $filtersWithoutPayment, $filters['start'], $filters['end'])
            ->join('penjualan_payments as payment_breakdown', 'payment_breakdown.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoinSub($paymentTotals, 'payment_breakdown_totals', 'payment_breakdown_totals.penjualan_transaction_id', '=', 'sales.id')
            ->when($filters['payment_method'], fn (Builder $query, string $method) => $query->where('payment_breakdown.metode', $method))
            ->select('payment_breakdown.metode as key')
            ->selectRaw("COALESCE(SUM({$returnRevenue} * CASE WHEN COALESCE(payment_breakdown_totals.amount, 0) > 0 THEN (1.0 * payment_breakdown.amount) / payment_breakdown_totals.amount ELSE 1 END), 0) as revenue")
            ->groupBy('payment_breakdown.metode')->get()->keyBy('key');
        $keys = $sales->keys()->merge($returns->keys())->unique();

        return $keys->map(function ($key) use ($sales, $returns, $netRevenue) {
            $revenue = round((float) ($sales->get($key)?->revenue ?? 0) - (float) ($returns->get($key)?->revenue ?? 0), 2);

            return [
                'key' => $key,
                'label' => PenjualanPosService::PAYMENT_METHODS[$key] ?? str($key)->replace('_', ' ')->title()->toString(),
                'transactions' => (int) ($sales->get($key)?->transactions ?? 0),
                'revenue' => $revenue,
                'contribution_percent' => $netRevenue != 0.0 ? round(($revenue / $netRevenue) * 100, 2) : 0,
            ];
        })->sortByDesc('revenue')->values()->all();
    }

    private function hourly(array $branchIds, array $filters): array
    {
        $hour = $this->hourExpression('sales.tanggal_transaksi');
        $sales = $this->saleLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->selectRaw("{$hour} as bucket")
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COALESCE(SUM('.$this->saleRevenueExpression().'), 0) as revenue')
            ->groupByRaw($hour)->get()->keyBy(fn ($row) => (int) $row->bucket);
        $returns = $this->returnLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->selectRaw("{$hour} as bucket")
            ->selectRaw('COALESCE(SUM('.$this->returnRevenueExpression().'), 0) as revenue')
            ->groupByRaw($hour)->get()->keyBy(fn ($row) => (int) $row->bucket);
        $rows = collect(range(0, 23))->map(function (int $value) use ($sales, $returns) {
            return [
                'hour' => $value,
                'label' => str_pad((string) $value, 2, '0', STR_PAD_LEFT).':00',
                'transactions' => (int) ($sales->get($value)?->transactions ?? 0),
                'revenue' => round((float) ($sales->get($value)?->revenue ?? 0) - (float) ($returns->get($value)?->revenue ?? 0), 2),
            ];
        });
        $peak = $rows->sortByDesc('revenue')->first();
        $busy = $rows->sortByDesc('transactions')->first();

        return [
            'rows' => $rows->all(),
            'peak_hour' => ($peak['revenue'] ?? 0) > 0 ? $peak['label'] : null,
            'peak_revenue' => (float) ($peak['revenue'] ?? 0),
            'busy_hour' => ($busy['transactions'] ?? 0) > 0 ? $busy['label'] : null,
            'busy_transactions' => (int) ($busy['transactions'] ?? 0),
        ];
    }

    private function weekdays(array $branchIds, array $filters): array
    {
        $weekday = $this->weekdayExpression('sales.tanggal_transaksi');
        $sales = $this->saleLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->selectRaw("{$weekday} as bucket")
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COUNT(DISTINCT '.$this->dateExpression('sales.tanggal_transaksi').') as active_days')
            ->selectRaw('COALESCE(SUM('.$this->saleRevenueExpression().'), 0) as revenue')
            ->groupByRaw($weekday)->get()->keyBy(fn ($row) => (int) $row->bucket);
        $returns = $this->returnLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->selectRaw("{$weekday} as bucket")
            ->selectRaw('COALESCE(SUM('.$this->returnRevenueExpression().'), 0) as revenue')
            ->groupByRaw($weekday)->get()->keyBy(fn ($row) => (int) $row->bucket);
        $rows = collect(range(1, 7))->map(function (int $value) use ($sales, $returns) {
            $revenue = round((float) ($sales->get($value)?->revenue ?? 0) - (float) ($returns->get($value)?->revenue ?? 0), 2);
            $activeDays = (int) ($sales->get($value)?->active_days ?? 0);

            return [
                'day' => $value,
                'label' => self::DAY_NAMES[$value],
                'transactions' => (int) ($sales->get($value)?->transactions ?? 0),
                'revenue' => $revenue,
                'average_revenue' => $activeDays > 0 ? round($revenue / $activeDays, 2) : 0,
            ];
        });
        $peak = $rows->sortByDesc('revenue')->first();

        return [
            'rows' => $rows->all(),
            'peak_day' => ($peak['revenue'] ?? 0) > 0 ? $peak['label'] : null,
            'peak_revenue' => (float) ($peak['revenue'] ?? 0),
        ];
    }

    private function cashiers(array $branchIds, array $filters, float $netRevenue): array
    {
        $sales = $this->saleLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->select('sales.completed_by as key')
            ->selectRaw("COALESCE(cashiers.name, 'Tanpa kasir') as label")
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM('.$this->saleRevenueExpression().'), 0) as revenue')
            ->groupBy('sales.completed_by', 'cashiers.name')->get()->keyBy(fn ($row) => (string) ($row->key ?? 0));
        $returns = $this->returnLines($branchIds, $filters, $filters['start'], $filters['end'])
            ->select('sales.completed_by as key')
            ->selectRaw('COALESCE(SUM('.$this->returnRevenueExpression().'), 0) as revenue')
            ->groupBy('sales.completed_by')->get()->keyBy(fn ($row) => (string) ($row->key ?? 0));

        return $sales->map(function ($sale, $key) use ($returns, $netRevenue) {
            $revenue = round((float) $sale->revenue - (float) ($returns->get($key)?->revenue ?? 0), 2);
            $transactions = (int) $sale->transactions;

            return [
                'id' => (int) $key ?: null,
                'name' => $sale->label,
                'transactions' => $transactions,
                'qty' => round((float) $sale->qty, 2),
                'revenue' => $revenue,
                'average_transaction' => $transactions > 0 ? round($revenue / $transactions, 2) : 0,
                'contribution_percent' => $netRevenue != 0.0 ? round(($revenue / $netRevenue) * 100, 2) : 0,
            ];
        })->sortByDesc('revenue')->values()->all();
    }

    private function target(array $branchIds, array $filters, float $netRevenue): array
    {
        $query = OmzetTargetModel::query()
            ->whereIn('branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->whereDate('period_start', $filters['start']->toDateString())
            ->whereDate('period_end', $filters['end']->toDateString());
        $configuredBranches = (clone $query)->count();
        $amount = round((float) $query->sum('target_amount'), 2);
        $achievement = $amount > 0 ? round(($netRevenue / $amount) * 100, 2) : 0;
        $status = match (true) {
            $amount <= 0 => 'Belum Diatur',
            $achievement >= 100 => 'Target Tercapai',
            $achievement >= 80 => 'Hampir Tercapai',
            default => 'Perlu Perhatian',
        };

        return [
            'amount' => $amount,
            'realization' => round($netRevenue, 2),
            'achievement_percent' => $achievement,
            'remaining' => round(max(0, $amount - $netRevenue), 2),
            'status' => $status,
            'configured_branches' => $configuredBranches,
            'expected_branches' => count($branchIds),
            'can_edit' => count($branchIds) === 1,
        ];
    }

    private function insights(
        array $summary,
        array $products,
        array $categories,
        array $payments,
        array $hourly,
        array $weekdays,
        array $marketBasket,
    ): array {
        $growth = (float) $summary['growth_percent'];
        $direction = $growth > 0 ? 'naik' : ($growth < 0 ? 'turun' : 'stabil');
        $tone = $growth > 0 ? 'positive' : ($growth < 0 ? 'negative' : 'neutral');
        $topProduct = $products[0] ?? null;
        $topCategory = $categories[0] ?? null;
        $topPayment = $payments[0] ?? null;

        return [
            [
                'icon' => $growth >= 0 ? 'mdi-trending-up' : 'mdi-trending-down',
                'tone' => $tone,
                'title' => 'Pergerakan omzet',
                'text' => 'Omzet bersih '.$direction.' '.number_format(abs($growth), 2, ',', '.').'% dibanding periode sebelumnya.',
            ],
            [
                'icon' => 'mdi-pill-multiple', 'tone' => 'blue', 'title' => 'Produk utama',
                'text' => $topProduct
                    ? $topProduct['name'].' menyumbang '.number_format($topProduct['contribution_percent'], 2, ',', '.').'% omzet bersih.'
                    : 'Belum ada produk terjual pada periode ini.',
            ],
            [
                'icon' => 'mdi-shape-outline', 'tone' => 'violet', 'title' => 'Kategori utama',
                'text' => $topCategory
                    ? $topCategory['label'].' menjadi kategori dengan omzet tertinggi.'
                    : 'Belum ada kategori penyumbang omzet.',
            ],
            [
                'icon' => 'mdi-clock-fast', 'tone' => 'orange', 'title' => 'Jam ramai',
                'text' => $hourly['busy_hour']
                    ? 'Transaksi paling ramai terjadi sekitar pukul '.$hourly['busy_hour'].' dengan '.$hourly['busy_transactions'].' transaksi.'
                    : 'Belum ada jam ramai pada periode ini.',
            ],
            [
                'icon' => 'mdi-calendar-star', 'tone' => 'teal', 'title' => 'Hari terbaik',
                'text' => $weekdays['peak_day']
                    ? $weekdays['peak_day'].' menjadi hari dengan omzet tertinggi.'
                    : 'Belum ada hari unggulan pada periode ini.',
            ],
            [
                'icon' => 'mdi-credit-card-check-outline', 'tone' => 'green', 'title' => 'Pembayaran dominan',
                'text' => $topPayment
                    ? $topPayment['label'].' berkontribusi '.number_format($topPayment['contribution_percent'], 2, ',', '.').'%.'
                    : 'Belum ada metode pembayaran dominan.',
            ],
            [
                'icon' => 'mdi-set-center', 'tone' => 'violet', 'title' => 'Peluang bundling',
                'text' => $marketBasket['summary']['leading_pair']
                    ? $marketBasket['summary']['leading_pair'].' menjadi pasangan teratas dengan lift '.number_format($marketBasket['summary']['leading_lift'], 2, ',', '.').'.'
                    : 'Belum ada pasangan produk pada transaksi yang dianalisis.',
            ],
        ];
    }

    private function mergeDimensionRows(Collection $sales, Collection $returns, float $netRevenue, bool $withQty = false): array
    {
        $keys = $sales->keys()->merge($returns->keys())->unique();

        return $keys->map(function ($key) use ($sales, $returns, $netRevenue, $withQty) {
            $sale = $sales->get($key);
            $return = $returns->get($key);
            $revenue = round((float) ($sale?->revenue ?? 0) - (float) ($return?->revenue ?? 0), 2);
            $row = [
                'label' => (string) $key,
                'revenue' => $revenue,
                'contribution_percent' => $netRevenue != 0.0 ? round(($revenue / $netRevenue) * 100, 2) : 0,
            ];
            if ($withQty) {
                $row['qty'] = round((float) ($sale?->qty ?? 0), 2);
                $row['return_qty'] = round((float) ($return?->qty ?? 0), 2);
            }

            return $row;
        })->sortByDesc('revenue')->values()->all();
    }

    private function options(array $branchIds, Collection $branches): array
    {
        $scopedIds = $branchIds === [] ? [-1] : $branchIds;
        $cashiers = DB::table('users')
            ->join('penjualan_transactions as sales', 'sales.completed_by', '=', 'users.id')
            ->whereIn('sales.branch_id', $scopedIds)
            ->where('sales.status', 'completed')
            ->distinct()->orderBy('users.name')->get(['users.id', 'users.name']);
        $shifts = DB::table('cashier_shifts as shifts')
            ->leftJoin('users', 'users.id', '=', 'shifts.user_id')
            ->whereIn('shifts.branch_id', $scopedIds)
            ->orderByDesc('shifts.opened_at')
            ->limit(500)
            ->get(['shifts.id', 'shifts.shift_number', 'users.name as cashier_name', 'shifts.opened_at']);
        $medicines = DB::table('master_obats')
            ->where('is_active', true)
            ->orderBy('nama_obat')
            ->get(['id', 'kode_obat', 'nama_obat']);
        $categories = DB::table('categories')->orderBy('name')->get(['id', 'name']);
        $classifications = DB::table('golongan_obats')->orderBy('nama')->get(['id', 'nama']);

        return [
            'branches' => $branches->map(fn ($branch) => [
                'id' => (int) $branch->id, 'code' => $branch->code, 'name' => $branch->name,
            ])->values()->all(),
            'cashiers' => $cashiers->map(fn ($cashier) => ['id' => (int) $cashier->id, 'name' => $cashier->name])->all(),
            'shifts' => $shifts->map(fn ($shift) => [
                'id' => (int) $shift->id,
                'label' => $shift->shift_number.' · '.($shift->cashier_name ?: 'Tanpa kasir'),
            ])->all(),
            'medicines' => $medicines->map(fn ($medicine) => [
                'id' => (int) $medicine->id,
                'label' => $medicine->nama_obat.' · '.$medicine->kode_obat,
            ])->all(),
            'categories' => $categories->map(fn ($category) => ['id' => (int) $category->id, 'name' => $category->name])->all(),
            'classifications' => $classifications->map(fn ($item) => ['id' => (int) $item->id, 'name' => $item->nama])->all(),
            'sale_types' => collect(PenjualanPosService::TRANSACTION_TYPES)->map(
                fn ($label, $key) => ['key' => $key, 'label' => $label]
            )->values()->all(),
            'payment_methods' => collect(PenjualanPosService::PAYMENT_METHODS)->map(
                fn ($label, $key) => ['key' => $key, 'label' => $label]
            )->values()->all(),
        ];
    }

    private function activeFilterLabels(array $filters, array $context): array
    {
        $labels = [
            $context['branch_label'],
            $this->dateLabel($filters['start']).' - '.$this->dateLabel($filters['end']),
        ];
        $map = [
            'cashier_id' => 'Kasir terpilih',
            'shift_id' => 'Shift terpilih',
            'medicine_id' => 'Obat terpilih',
            'category_id' => 'Kategori terpilih',
            'golongan_id' => 'Golongan terpilih',
            'transaction_type' => PenjualanPosService::TRANSACTION_TYPES[$filters['transaction_type'] ?? ''] ?? null,
            'payment_method' => PenjualanPosService::PAYMENT_METHODS[$filters['payment_method'] ?? ''] ?? null,
        ];
        foreach ($map as $key => $label) {
            if (! empty($filters[$key]) && $label) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    private function saleLines(array $branchIds, array $filters, Carbon $start, Carbon $end): Builder
    {
        $query = DB::table('penjualan_transactions as sales')
            ->join('penjualan_transaction_details as details', 'details.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoinSub($this->detailTotalsSubquery(), 'detail_totals', 'detail_totals.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoin('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->leftJoin('golongan_obats as classifications', 'classifications.id', '=', 'medicines.golongan_id')
            ->leftJoin('users as cashiers', 'cashiers.id', '=', 'sales.completed_by')
            ->leftJoin('cashier_shifts as shifts', 'shifts.id', '=', 'sales.cashier_shift_id')
            ->whereIn('sales.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        $this->applyDimensionFilters($query, $filters, 'details');
        $this->applyPaymentFactorJoins($query, $filters);

        return $query;
    }

    private function returnLines(array $branchIds, array $filters, Carbon $start, Carbon $end): Builder
    {
        $query = DB::table('retur_penjualan as returns')
            ->join('retur_penjualan_details as return_details', 'return_details.retur_penjualan_id', '=', 'returns.id')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'returns.penjualan_transaction_id')
            ->leftJoin('master_obats as medicines', 'medicines.id', '=', 'return_details.obat_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->leftJoin('golongan_obats as classifications', 'classifications.id', '=', 'medicines.golongan_id')
            ->leftJoin('users as cashiers', 'cashiers.id', '=', 'sales.completed_by')
            ->leftJoin('cashier_shifts as shifts', 'shifts.id', '=', 'sales.cashier_shift_id')
            ->whereIn('returns.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('returns.status', 'posted')
            ->where('sales.status', 'completed')
            ->whereBetween(DB::raw($this->dateExpression('returns.tanggal_retur')), [$start->toDateString(), $end->toDateString()]);

        $this->applyDimensionFilters($query, $filters, 'return_details');
        $this->applyPaymentFactorJoins($query, $filters);

        return $query;
    }

    private function applyDimensionFilters(Builder $query, array $filters, string $detailAlias): void
    {
        $query
            ->when($filters['cashier_id'] ?? null, fn (Builder $builder, int $id) => $builder->where('sales.completed_by', $id))
            ->when($filters['shift_id'] ?? null, fn (Builder $builder, int $id) => $builder->where('sales.cashier_shift_id', $id))
            ->when($filters['medicine_id'] ?? null, fn (Builder $builder, int $id) => $builder->where("{$detailAlias}.obat_id", $id))
            ->when($filters['category_id'] ?? null, fn (Builder $builder, int $id) => $builder->where('medicines.category_id', $id))
            ->when($filters['golongan_id'] ?? null, fn (Builder $builder, int $id) => $builder->where('medicines.golongan_id', $id))
            ->when($filters['transaction_type'] ?? null, fn (Builder $builder, string $type) => $builder->where('sales.jenis_transaksi', $type));
    }

    private function applyPaymentFactorJoins(Builder $query, array $filters): void
    {
        if (empty($filters['payment_method'])) {
            $query
                ->leftJoinSub($this->paymentTotalsSubquery(), 'selected_payment', 'selected_payment.penjualan_transaction_id', '=', 'sales.id')
                ->leftJoinSub($this->paymentTotalsSubquery(), 'selected_payment_total', 'selected_payment_total.penjualan_transaction_id', '=', 'sales.id');

            return;
        }

        $selected = DB::table('penjualan_payments')
            ->select('penjualan_transaction_id')
            ->selectRaw('SUM(amount) as amount')
            ->where('metode', $filters['payment_method'])
            ->groupBy('penjualan_transaction_id');
        $query
            ->joinSub($selected, 'selected_payment', 'selected_payment.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoinSub($this->paymentTotalsSubquery(), 'selected_payment_total', 'selected_payment_total.penjualan_transaction_id', '=', 'sales.id');
    }

    private function saleRevenueExpression(): string
    {
        return '(sales.grand_total * '.$this->lineWeightExpression().' * '.$this->paymentFactorExpression().')';
    }

    private function saleGrossExpression(): string
    {
        return '(details.subtotal_gross * '.$this->paymentFactorExpression().')';
    }

    private function saleDiscountExpression(): string
    {
        return '((details.diskon_nominal + (sales.diskon_transaksi_nominal * '.$this->lineWeightExpression().')) * '.$this->paymentFactorExpression().')';
    }

    private function returnRevenueExpression(): string
    {
        return '(return_details.total * '.$this->paymentFactorExpression().')';
    }

    private function lineWeightExpression(): string
    {
        return '(CASE WHEN COALESCE(detail_totals.subtotal_net, 0) > 0 '
            .'THEN (1.0 * details.subtotal_net) / detail_totals.subtotal_net '
            .'ELSE 1.0 / NULLIF(detail_totals.item_count, 0) END)';
    }

    private function paymentFactorExpression(): string
    {
        return '(CASE WHEN selected_payment.penjualan_transaction_id IS NULL THEN 1 '
            .'WHEN COALESCE(selected_payment_total.amount, 0) > 0 THEN (1.0 * selected_payment.amount) / selected_payment_total.amount '
            .'ELSE 1 END)';
    }

    private function detailTotalsSubquery(): Builder
    {
        return DB::table('penjualan_transaction_details')
            ->select('penjualan_transaction_id')
            ->selectRaw('COUNT(id) as item_count')
            ->selectRaw('COALESCE(SUM(subtotal_net), 0) as subtotal_net')
            ->groupBy('penjualan_transaction_id');
    }

    private function paymentTotalsSubquery(): Builder
    {
        return DB::table('penjualan_payments')
            ->select('penjualan_transaction_id')
            ->selectRaw('COALESCE(SUM(amount), 0) as amount')
            ->groupBy('penjualan_transaction_id');
    }

    private function productKey(mixed $id, mixed $code, mixed $name): string
    {
        return $id !== null ? 'id:'.(int) $id : 'snapshot:'.trim((string) $code).'|'.trim((string) $name);
    }

    private function growth(float $current, float $previous): float
    {
        if (abs($previous) < 0.00001) {
            return abs($current) < 0.00001 ? 0 : 100;
        }

        return round((($current - $previous) / abs($previous)) * 100, 2);
    }

    private function bucketKey(Carbon $date, string $granularity): string
    {
        return match ($granularity) {
            'week' => $date->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
            'month' => $date->format('Y-m'),
            'year' => $date->format('Y'),
            default => $date->toDateString(),
        };
    }

    private function bucketLabel(string $key, string $granularity): string
    {
        if ($granularity === 'year') {
            return $key;
        }
        if ($granularity === 'month') {
            $date = Carbon::createFromFormat('Y-m', $key);

            return self::MONTH_NAMES[(int) $date->format('n')].' '.$date->format('Y');
        }
        $date = Carbon::parse($key);
        $label = $date->format('d').' '.self::MONTH_NAMES[(int) $date->format('n')];

        return $granularity === 'week' ? 'Pekan '.$label : $label;
    }

    private function dateLabel(Carbon $date): string
    {
        return $date->format('d').' '.self::MONTH_NAMES[(int) $date->format('n')].' '.$date->format('Y');
    }

    private function dateExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m-%d')";
    }

    private function hourExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', {$column}) AS INTEGER)"
            : "HOUR({$column})";
    }

    private function weekdayExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CASE strftime('%w', {$column}) WHEN '0' THEN 7 ELSE CAST(strftime('%w', {$column}) AS INTEGER) END"
            : "(WEEKDAY({$column}) + 1)";
    }
}
