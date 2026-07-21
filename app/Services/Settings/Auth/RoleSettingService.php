<?php

namespace App\Services\Settings\Auth;

use App\Models\BranchModel;
use App\Models\RoleSetting;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class RoleSettingService
{
    public const SAME_BRANCH = 'same_branch';

    public const ALL_BRANCHES = 'all_branches';

    public function rolesWithSettings(bool $includeUserCount = true): Collection
    {
        $query = Role::query()->orderBy('name');

        if ($includeUserCount) {
            $query->withCount('users');
        }

        $roles = $query->get();
        $stored = $this->storedSettings($roles->pluck('id')->all());

        return $roles->map(function (Role $role) use ($stored, $includeUserCount) {
            $setting = $this->resolveSetting($role, $stored->get($role->id));

            return [
                'role_id' => (int) $role->id,
                'name' => $role->name,
                'initials' => $this->initials($role->name),
                'user_count' => $includeUserCount ? (int) $role->users_count : 0,
                ...$setting,
            ];
        })->values();
    }

    public function update(array $settings): Collection
    {
        DB::transaction(function () use ($settings) {
            foreach ($settings as $setting) {
                RoleSetting::updateOrCreate(
                    ['role_id' => (int) $setting['role_id']],
                    [
                        'can_view_all_branches' => (bool) ($setting['can_view_all_branches'] ?? false),
                        'is_approver' => (bool) ($setting['is_approver'] ?? false),
                        'approval_scope' => $this->scope($setting['approval_scope'] ?? null),
                        'receives_notifications' => (bool) ($setting['receives_notifications'] ?? false),
                        'notification_scope' => $this->scope($setting['notification_scope'] ?? null),
                    ]
                );
            }
        });

        return $this->rolesWithSettings();
    }

    public function userCanManage(?User $user): bool
    {
        return (bool) $user && (
            $user->hasAnyRole(['Admin', 'admin', 'Super Admin', 'super admin'])
            || $user->can('MEDCARE.SETTINGS.AUTH')
        );
    }

    public function userCanViewAllBranches(?User $user): bool
    {
        return $this->userSettings($user)
            ->contains(fn (array $setting) => $setting['can_view_all_branches']);
    }

    public function userIsApprover(?User $user): bool
    {
        return $this->userSettings($user)
            ->contains(fn (array $setting) => $setting['is_approver']);
    }

    public function userCanApproveAllBranches(?User $user): bool
    {
        return $this->userSettings($user)->contains(
            fn (array $setting) => $setting['is_approver']
                && $setting['approval_scope'] === self::ALL_BRANCHES
        );
    }

    public function canApproveBranch(?User $user, ?int $branchId): bool
    {
        if (! $user || ! $this->userIsApprover($user)) {
            return false;
        }

        if ($this->userCanApproveAllBranches($user)) {
            return true;
        }

        return $branchId !== null
            && in_array($branchId, BranchAccess::assignedUserBranchIds($user), true);
    }

    public function approvalBranchIds(?User $user): array
    {
        if (! $this->userIsApprover($user)) {
            return [];
        }

        if ($this->userCanApproveAllBranches($user)) {
            return BranchModel::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return BranchAccess::assignedUserBranchIds($user);
    }

    public function approverRoleNames(): array
    {
        return $this->rolesWithSettings(false)
            ->where('is_approver', true)
            ->pluck('name')
            ->values()
            ->all();
    }

    public function approvalRecipients(?int $branchId): Collection
    {
        return $this->recipients('is_approver', 'approval_scope', $branchId);
    }

    public function notificationRecipients(?int $branchId): Collection
    {
        return $this->recipients('receives_notifications', 'notification_scope', $branchId);
    }

    private function recipients(string $enabledKey, string $scopeKey, ?int $branchId): Collection
    {
        $settings = $this->rolesWithSettings(false)->where($enabledKey, true);
        $allBranchRoleIds = $settings->where($scopeKey, self::ALL_BRANCHES)->pluck('role_id')->all();
        $sameBranchRoleIds = $settings->where($scopeKey, self::SAME_BRANCH)->pluck('role_id')->all();
        $recipients = collect();

        if (! empty($allBranchRoleIds)) {
            $recipients = $recipients->merge($this->usersWithRoles($allBranchRoleIds)->get());
        }

        if (! empty($sameBranchRoleIds)) {
            $query = $this->usersWithRoles($sameBranchRoleIds);

            if ($branchId !== null) {
                $this->scopeUsersToBranch($query, $branchId);
            }

            $recipients = $recipients->merge($query->get());
        }

        return $recipients->unique('id')->values();
    }

    private function usersWithRoles(array $roleIds)
    {
        return User::query()->whereHas(
            'roles',
            fn ($query) => $query->whereIn('roles.id', $roleIds)
        );
    }

    private function scopeUsersToBranch($query, int $branchId): void
    {
        $usersTable = (new User)->getTable();
        $hasDirectBranchColumn = Schema::hasColumn($usersTable, 'branch_id');

        $query->where(function ($userQuery) use ($branchId, $hasDirectBranchColumn, $usersTable) {
            if ($hasDirectBranchColumn) {
                $userQuery->where($usersTable.'.branch_id', $branchId)
                    ->orWhereHas('branches', fn ($branchQuery) => $branchQuery->where('branches.id', $branchId));

                return;
            }

            $userQuery->whereHas('branches', fn ($branchQuery) => $branchQuery->where('branches.id', $branchId));
        });
    }

    private function userSettings(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $roles = $user->roles()->get();
        $stored = $this->storedSettings($roles->pluck('id')->all());

        return $roles->map(fn (Role $role) => $this->resolveSetting($role, $stored->get($role->id)));
    }

    private function storedSettings(array $roleIds): Collection
    {
        if (empty($roleIds) || ! Schema::hasTable('role_settings')) {
            return collect();
        }

        return RoleSetting::query()->whereIn('role_id', $roleIds)->get()->keyBy('role_id');
    }

    private function resolveSetting(Role $role, ?RoleSetting $setting): array
    {
        $defaults = $this->defaultsForRole($role->name);

        if (! $setting) {
            return $defaults;
        }

        return [
            'can_view_all_branches' => (bool) $setting->can_view_all_branches,
            'is_approver' => (bool) $setting->is_approver,
            'approval_scope' => $this->scope($setting->approval_scope),
            'receives_notifications' => (bool) $setting->receives_notifications,
            'notification_scope' => $this->scope($setting->notification_scope),
        ];
    }

    private function defaultsForRole(string $roleName): array
    {
        $key = strtolower(trim($roleName));
        $isDefaultApprover = in_array($key, ['admin', 'apoteker'], true);

        return [
            'can_view_all_branches' => in_array($key, ['admin', 'super admin'], true),
            'is_approver' => $isDefaultApprover,
            'approval_scope' => self::SAME_BRANCH,
            'receives_notifications' => false,
            'notification_scope' => self::SAME_BRANCH,
        ];
    }

    private function scope(?string $scope): string
    {
        return $scope === self::ALL_BRANCHES ? self::ALL_BRANCHES : self::SAME_BRANCH;
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
