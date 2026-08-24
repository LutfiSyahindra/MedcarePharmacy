<?php

namespace App\Support;

use App\Models\Menu\Stok\StockOpnameModel;
use App\Models\User;
use App\Services\Settings\Auth\RoleSettingService;

class StockOpnameAccess
{
    public function __construct(private readonly RoleSettingService $roleSettings) {}

    public function activeLock(?User $user = null): ?StockOpnameModel
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        // Modul stok belum memiliki pemilih cabang per request. Pengguna lintas
        // cabang dikunci bila salah satu cabang yang dapat dilihat sedang dihitung,
        // agar kuantitas cabang opname tidak bocor dari tabel gabungan.
        $branchIds = BranchAccess::userBranchIds($user);

        if (empty($branchIds)) {
            return null;
        }

        return StockOpnameModel::query()
            ->with(['branch:id,name', 'rack:id,kode,nama'])
            ->whereIn('branch_id', $branchIds)
            ->whereIn('status', StockOpnameModel::lockingStatuses())
            ->oldest('started_at')
            ->first();
    }

    public function activeLockForBranches(array $branchIds): ?StockOpnameModel
    {
        $branchIds = collect($branchIds)->filter()->map(fn ($id) => (int) $id)->unique()->all();

        if (empty($branchIds)) {
            return null;
        }

        return StockOpnameModel::query()
            ->with(['branch:id,name', 'rack:id,kode,nama'])
            ->whereIn('branch_id', $branchIds)
            ->whereIn('status', StockOpnameModel::lockingStatuses())
            ->oldest('started_at')
            ->first();
    }

    public function stockMenusAreLocked(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->activeLock($user) !== null
            && ! $this->roleSettings->userCanViewStockDuringOpname($user);
    }

    public function userCanViewStockDuringOpname(?User $user = null): bool
    {
        return $this->roleSettings->userCanViewStockDuringOpname($user ?? auth()->user());
    }
}
