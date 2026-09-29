<?php

use App\Support\SidebarPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            $table->boolean('uses_manual_po_number')
                ->default(false)
                ->after('is_active')
                ->index();
        });

        $now = now();
        DB::table('permissions')->insertOrIgnore([
            'name' => SidebarPermissions::SETTINGS_PURCHASE_ORDER,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissionId = DB::table('permissions')
            ->where('name', SidebarPermissions::SETTINGS_PURCHASE_ORDER)
            ->where('guard_name', 'web')
            ->value('id');

        $adminRoleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn(DB::raw('LOWER(name)'), ['admin', 'super admin'])
            ->pluck('id');

        if ($permissionId) {
            DB::table('role_has_permissions')->insertOrIgnore(
                $adminRoleIds->map(static fn ($roleId): array => [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ])->all()
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('name', SidebarPermissions::SETTINGS_PURCHASE_ORDER)
            ->where('guard_name', 'web')
            ->delete();

        Schema::table('distributors', function (Blueprint $table) {
            $table->dropIndex(['uses_manual_po_number']);
            $table->dropColumn('uses_manual_po_number');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
