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
        Schema::create('surat_pesanan_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('golongan_id')->nullable()->constrained('golongan_obats')->cascadeOnDelete();
            $table->foreignId('main_golongan_id')->nullable()->constrained('main_golongan_obats')->cascadeOnDelete();
            $table->foreignId('sub_golongan_id')->nullable()->constrained('sub_golongan_obats')->cascadeOnDelete();
            $table->string('sp_type', 20);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('golongan_id');
            $table->unique('main_golongan_id');
            $table->unique('sub_golongan_id');
            $table->index('sp_type');
        });

        $now = now();
        DB::table('permissions')->insertOrIgnore([
            'name' => SidebarPermissions::SETTINGS_SURAT_PESANAN,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissionId = DB::table('permissions')
            ->where('name', SidebarPermissions::SETTINGS_SURAT_PESANAN)
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
        Schema::dropIfExists('surat_pesanan_settings');

        DB::table('permissions')
            ->where('name', SidebarPermissions::SETTINGS_SURAT_PESANAN)
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
