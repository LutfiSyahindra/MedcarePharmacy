<?php

use App\Support\SidebarPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->insertOrIgnore([
            'name' => SidebarPermissions::KEUANGAN,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissionId = DB::table('permissions')
            ->where('name', SidebarPermissions::KEUANGAN)
            ->where('guard_name', 'web')
            ->value('id');

        $roleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->where(function ($query) {
                $query->whereIn(DB::raw('LOWER(name)'), ['admin', 'super admin'])
                    ->orWhereIn('id', function ($subQuery) {
                        $subQuery->select('role_id')
                            ->from('role_has_permissions')
                            ->whereIn('permission_id', function ($permissionQuery) {
                                $permissionQuery->select('id')
                                    ->from('permissions')
                                    ->where('name', SidebarPermissions::PENJUALAN)
                                    ->where('guard_name', 'web');
                            });
                    });
            })
            ->pluck('id');

        if ($permissionId) {
            DB::table('role_has_permissions')->insertOrIgnore(
                $roleIds->map(fn ($roleId) => [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ])->all(),
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('name', SidebarPermissions::KEUANGAN)
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
