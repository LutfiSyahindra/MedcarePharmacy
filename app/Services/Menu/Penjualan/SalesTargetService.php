<?php

namespace App\Services\Menu\Penjualan;

use App\Models\BranchModel;
use App\Models\Menu\Analisis\OmzetTargetModel;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesTargetService
{
    private const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function branches(User $user): Collection
    {
        $allowedIds = BranchAccess::userBranchIds($user);

        return BranchModel::query()
            ->whereIn('id', $allowedIds === [] ? [-1] : $allowedIds)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);
    }

    public function dashboard(User $user, int $year, ?int $branchId = null): array
    {
        $branches = $this->branches($user);
        $this->ensureBranchAccess($branches, $branchId);

        $scopedBranches = $branchId === null
            ? $branches
            : $branches->where('id', $branchId)->values();
        $branchIds = $scopedBranches->pluck('id')->map(fn ($id) => (int) $id)->all();
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = $start->copy()->endOfYear();

        $targets = OmzetTargetModel::query()
            ->whereIn('branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->whereBetween('period_start', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->filter(fn (OmzetTargetModel $target) => $this->isMonthlyTarget($target));
        $sales = $this->salesByMonth($branchIds, $start, $end);
        $returns = $this->returnsByMonth($branchIds, $start, $end);

        $rows = collect(range(1, 12))->map(function (int $month) use ($year, $targets, $sales, $returns, $scopedBranches) {
            $period = sprintf('%04d-%02d', $year, $month);
            $periodTargets = $targets->filter(fn (OmzetTargetModel $target) => $target->period_start->format('Y-m') === $period);
            $amount = round((float) $periodTargets->sum('target_amount'), 2);
            $gross = round($this->metricSum($sales, $period, 'amount'), 2);
            $returned = round($this->metricSum($returns, $period, 'amount'), 2);
            $realization = round($gross - $returned, 2);
            $configuredBranches = $periodTargets->pluck('branch_id')->unique()->count();

            return $this->targetRow(
                period: $period,
                label: self::MONTH_NAMES[$month].' '.$year,
                amount: $amount,
                realization: $realization,
                gross: $gross,
                returned: $returned,
                transactions: (int) $this->metricSum($sales, $period, 'transactions'),
                configuredBranches: $configuredBranches,
                expectedBranches: $scopedBranches->count(),
                targetId: $scopedBranches->count() === 1 ? $periodTargets->first()?->id : null,
            );
        })->values();

        $branchRows = $scopedBranches->map(function (BranchModel $branch) use ($targets, $sales, $returns) {
            $branchTargets = $targets->where('branch_id', $branch->id);
            $amount = round((float) $branchTargets->sum('target_amount'), 2);
            $gross = round($this->metricSum($sales, null, 'amount', (int) $branch->id), 2);
            $returned = round($this->metricSum($returns, null, 'amount', (int) $branch->id), 2);
            $realization = round($gross - $returned, 2);

            return [
                'id' => (int) $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'target' => $amount,
                'realization' => $realization,
                'remaining' => round(max(0, $amount - $realization), 2),
                'achievement_percent' => $this->achievement($amount, $realization),
                'configured_months' => $branchTargets->count(),
                'achieved_months' => $this->achievedMonthsForBranch(
                    (int) $branch->id,
                    $branchTargets,
                    $sales,
                    $returns,
                ),
            ];
        })->values();

        $targetTotal = round((float) $rows->sum('target'), 2);
        $realizationTotal = round((float) $rows->sum('realization'), 2);
        $currentPeriod = sprintf('%04d-%02d', $year, (int) now()->format('m'));

        return [
            'meta' => [
                'year' => $year,
                'branch_id' => $branchId,
                'branch_label' => $branchId === null
                    ? ($scopedBranches->count() > 1 ? 'Semua cabang' : ($scopedBranches->first()?->name ?? 'Belum ada cabang'))
                    : ($scopedBranches->first()?->name ?? 'Cabang'),
                'current_period' => $currentPeriod,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'summary' => [
                'target' => $targetTotal,
                'realization' => $realizationTotal,
                'remaining' => round(max(0, $targetTotal - $realizationTotal), 2),
                'achievement_percent' => $this->achievement($targetTotal, $realizationTotal),
                'configured_months' => (int) $rows->where('configured_branches', '>', 0)->count(),
                'achieved_months' => (int) $rows->where('status_key', 'achieved')->count(),
                'expected_configurations' => $scopedBranches->count() * 12,
                'configured_configurations' => $targets->count(),
            ],
            'rows' => $rows->all(),
            'branches' => $branchRows->all(),
            'options' => [
                'branches' => $branches->map(fn (BranchModel $branch) => [
                    'id' => (int) $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name,
                ])->values()->all(),
            ],
        ];
    }

    public function save(User $user, int $branchId, string $period, float $amount): OmzetTargetModel
    {
        $branches = $this->branches($user);
        $this->ensureBranchAccess($branches, $branchId);
        $start = Carbon::createFromFormat('!Y-m', $period)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $target = OmzetTargetModel::query()->firstOrNew([
            'branch_id' => $branchId,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
        ]);
        if (! $target->exists) {
            $target->created_by = $user->id;
        }
        $target->target_amount = round(max(0, $amount), 2);
        $target->updated_by = $user->id;
        $target->save();

        return $target->fresh('branch');
    }

    public function delete(User $user, OmzetTargetModel $target): void
    {
        $branches = $this->branches($user);
        $this->ensureBranchAccess($branches, (int) $target->branch_id);

        if (! $this->isMonthlyTarget($target)) {
            throw ValidationException::withMessages([
                'target' => 'Target periode bebas tidak dapat dihapus dari halaman target bulanan.',
            ]);
        }

        $target->delete();
    }

    private function ensureBranchAccess(Collection $branches, ?int $branchId): void
    {
        if ($branchId !== null && ! $branches->contains('id', $branchId)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang tidak tersedia untuk akun ini.',
            ]);
        }
    }

    private function isMonthlyTarget(OmzetTargetModel $target): bool
    {
        return $target->period_start->isSameDay($target->period_start->copy()->startOfMonth())
            && $target->period_end->isSameDay($target->period_start->copy()->endOfMonth());
    }

    private function salesByMonth(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        $month = $this->monthExpression('tanggal_transaksi');

        return DB::table('penjualan_transactions')
            ->select('branch_id')
            ->selectRaw("{$month} as period")
            ->selectRaw('COALESCE(SUM(grand_total), 0) as amount')
            ->selectRaw('COUNT(id) as transactions')
            ->whereIn('branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->groupBy('branch_id', DB::raw($month))
            ->get();
    }

    private function returnsByMonth(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        $month = $this->monthExpression('tanggal_retur');

        return DB::table('retur_penjualan')
            ->select('branch_id')
            ->selectRaw("{$month} as period")
            ->selectRaw('COALESCE(SUM(grand_total), 0) as amount')
            ->whereIn('branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('status', 'posted')
            ->whereBetween('tanggal_retur', [$start->toDateString(), $end->toDateString()])
            ->groupBy('branch_id', DB::raw($month))
            ->get();
    }

    private function metricSum(Collection $metrics, ?string $period, string $column, ?int $branchId = null): float
    {
        return (float) $metrics
            ->when($period !== null, fn (Collection $rows) => $rows->where('period', $period))
            ->when($branchId !== null, fn (Collection $rows) => $rows->where('branch_id', $branchId))
            ->sum($column);
    }

    private function targetRow(
        string $period,
        string $label,
        float $amount,
        float $realization,
        float $gross,
        float $returned,
        int $transactions,
        int $configuredBranches,
        int $expectedBranches,
        ?int $targetId,
    ): array {
        $achievement = $this->achievement($amount, $realization);
        [$status, $statusKey] = $this->status($period, $amount, $achievement, $configuredBranches);

        return [
            'period' => $period,
            'label' => $label,
            'target_id' => $targetId,
            'target' => $amount,
            'gross_sales' => $gross,
            'returns' => $returned,
            'realization' => $realization,
            'remaining' => round(max(0, $amount - $realization), 2),
            'variance' => round($realization - $amount, 2),
            'achievement_percent' => $achievement,
            'transactions' => $transactions,
            'configured_branches' => $configuredBranches,
            'expected_branches' => $expectedBranches,
            'status' => $status,
            'status_key' => $statusKey,
        ];
    }

    private function achievedMonthsForBranch(
        int $branchId,
        Collection $targets,
        Collection $sales,
        Collection $returns,
    ): int {
        return $targets->filter(function (OmzetTargetModel $target) use ($branchId, $sales, $returns) {
            $period = $target->period_start->format('Y-m');
            $realization = $this->metricSum($sales, $period, 'amount', $branchId)
                - $this->metricSum($returns, $period, 'amount', $branchId);

            return (float) $target->target_amount > 0 && $realization >= (float) $target->target_amount;
        })->count();
    }

    private function achievement(float $target, float $realization): float
    {
        return $target > 0 ? round(($realization / $target) * 100, 2) : 0;
    }

    private function status(string $period, float $target, float $achievement, int $configured): array
    {
        if ($configured === 0 || $target <= 0) {
            return ['Belum Diatur', 'unset'];
        }
        if ($achievement >= 100) {
            return ['Tercapai', 'achieved'];
        }
        if ($period > now()->format('Y-m')) {
            return ['Akan Datang', 'upcoming'];
        }
        if ($achievement >= 80) {
            return ['Hampir Tercapai', 'near'];
        }

        return ['Perlu Perhatian', 'attention'];
    }

    private function monthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }
}
