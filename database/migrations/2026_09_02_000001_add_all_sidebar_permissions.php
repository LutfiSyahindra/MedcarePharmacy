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

        DB::table('permissions')->insertOrIgnore(
            collect(SidebarPermissions::all())
                ->map(static fn (string $permission): array => [
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all(),
        );

        $this->grantAllSidebarPermissionsToAdministrators();
        $this->preserveExistingSidebarAccess();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', SidebarPermissions::additions())
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function grantAllSidebarPermissionsToAdministrators(): void
    {
        $roleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn(DB::raw('LOWER(name)'), ['admin', 'super admin'])
            ->pluck('id');

        $this->grantPermissionsToRoles($roleIds, SidebarPermissions::all());
    }

    private function preserveExistingSidebarAccess(): void
    {
        $dashboardPermissionId = DB::table('permissions')
            ->where('name', SidebarPermissions::DASHBOARD)
            ->where('guard_name', 'web')
            ->value('id');

        if ($dashboardPermissionId) {
            $operationalRoleIds = DB::table('role_has_permissions')
                ->where('permission_id', $dashboardPermissionId)
                ->pluck('role_id');

            $this->grantPermissionsToRoles($operationalRoleIds, [
                SidebarPermissions::PASIEN,
                SidebarPermissions::DOKUMEN,
                SidebarPermissions::ANALISIS_PERSEDIAAN,
                SidebarPermissions::ANALISIS_PENJUALAN,
                SidebarPermissions::ANALISIS_PROFITABILITAS,
                SidebarPermissions::ANALISIS_PENGADAAN,
                SidebarPermissions::LAPORAN,
            ]);
        }

        $authPermissionId = DB::table('permissions')
            ->where('name', SidebarPermissions::SETTINGS_AUTH)
            ->where('guard_name', 'web')
            ->value('id');

        if ($authPermissionId) {
            $authRoleIds = DB::table('role_has_permissions')
                ->where('permission_id', $authPermissionId)
                ->pluck('role_id');

            $this->grantPermissionsToRoles($authRoleIds, [SidebarPermissions::SETTINGS_ROLE_SETTING]);
        }

        $pharmacistRoleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->where(DB::raw('LOWER(name)'), 'apoteker')
            ->pluck('id');

        $this->grantPermissionsToRoles($pharmacistRoleIds, [SidebarPermissions::SETTINGS_NOTIFIKASI]);
    }

    /**
     * @param  iterable<int>  $roleIds
     * @param  list<string>  $permissionNames
     */
    private function grantPermissionsToRoles(iterable $roleIds, array $permissionNames): void
    {
        $roleIds = collect($roleIds)->map(static fn ($id) => (int) $id)->all();

        if ($roleIds === [] || $permissionNames === []) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        $rows = [];
        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                $rows[] = [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ];
            }
        }

        DB::table('role_has_permissions')->insertOrIgnore($rows);
    }
};
