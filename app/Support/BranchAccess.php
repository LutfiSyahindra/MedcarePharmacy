<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BranchAccess
{
    public static function userBranchIds(?User $user = null): array
    {
        $user = $user ?: Auth::user();

        if (! $user) {
            return [];
        }

        $branchIds = $user->branches()->pluck('branches.id')->all();

        if (! empty($user->branch_id)) {
            $branchIds[] = $user->branch_id;
        }

        return collect($branchIds)
            ->filter()
            ->map(fn ($branchId) => (int) $branchId)
            ->unique()
            ->values()
            ->all();
    }

    public static function userBranchId(?User $user = null): ?int
    {
        return self::userBranchIds($user)[0] ?? null;
    }

    public static function requireUserBranchId(?User $user = null): int
    {
        $branchId = self::userBranchId($user);

        if (! $branchId) {
            throw ValidationException::withMessages([
                'branch_id' => 'User belum ditugaskan ke branch.',
            ]);
        }

        return $branchId;
    }

    public static function scope(Builder $query, string $column = 'branch_id', ?array $branchIds = null): Builder
    {
        $branchIds = $branchIds ?? self::userBranchIds();

        if (empty($branchIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $branchIds);
    }
}
