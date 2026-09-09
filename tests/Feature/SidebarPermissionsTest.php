<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SidebarPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SidebarPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_sidebar_permission_is_registered(): void
    {
        $registeredPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        $this->assertEmpty(array_diff(SidebarPermissions::all(), $registeredPermissions));
    }

    public function test_sidebar_only_displays_modules_allowed_for_the_user(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Petugas Stok', 'guard_name' => 'web']);
        $role->givePermissionTo(SidebarPermissions::STOK);
        $user->assignRole($role);

        $this->actingAs($user);

        $sidebar = view('template.partials.sidebar')->render();

        $this->assertStringContainsString('<span class="link-title">Stok</span>', $sidebar);
        $this->assertStringNotContainsString('<span class="link-title">Pembelian</span>', $sidebar);
        $this->assertStringNotContainsString('<span class="link-title">Master Obat</span>', $sidebar);
        $this->assertStringNotContainsString('<span class="link-title">Dashboard</span>', $sidebar);
    }
}
