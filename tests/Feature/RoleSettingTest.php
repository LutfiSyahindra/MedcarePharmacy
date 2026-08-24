<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\RoleSetting;
use App\Models\User;
use App\Services\Menu\Penjualan\PenjualanPosService;
use App\Services\Settings\Auth\RoleSettingService;
use App\Support\BranchAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_open_and_save_role_settings(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $userRole = Role::create(['name' => 'Users', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->get(route('settings.role-setting.index'))
            ->assertOk()
            ->assertSee('Role Setting')
            ->assertSee('Users');

        $this->actingAs($admin)
            ->putJson(route('settings.role-setting.update'), [
                'settings' => [
                    [
                        'role_id' => $adminRole->id,
                        'can_view_all_branches' => true,
                        'pos_scope' => 'all_branches',
                        'is_approver' => true,
                        'approval_scope' => 'all_branches',
                        'is_stock_opname_validator' => true,
                        'can_view_stock_during_opname' => true,
                        'receives_notifications' => false,
                        'notification_scope' => 'same_branch',
                    ],
                    [
                        'role_id' => $userRole->id,
                        'can_view_all_branches' => false,
                        'pos_scope' => 'same_branch',
                        'is_approver' => false,
                        'approval_scope' => 'same_branch',
                        'is_stock_opname_validator' => false,
                        'can_view_stock_during_opname' => false,
                        'receives_notifications' => true,
                        'notification_scope' => 'same_branch',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('summary.pos_all_branch', 1)
            ->assertJsonPath('summary.approver', 1)
            ->assertJsonPath('summary.stock_opname_validator', 1)
            ->assertJsonPath('summary.stock_opname_stock_viewer', 1)
            ->assertJsonPath('summary.notification', 1);

        $this->assertDatabaseHas('role_settings', [
            'role_id' => $userRole->id,
            'pos_scope' => 'same_branch',
            'is_stock_opname_validator' => false,
            'can_view_stock_during_opname' => false,
            'receives_notifications' => true,
            'notification_scope' => 'same_branch',
        ]);
    }

    public function test_branch_and_approval_scope_follow_role_configuration(): void
    {
        $branchA = BranchModel::create(['code' => 'A', 'name' => 'Branch A']);
        $branchB = BranchModel::create(['code' => 'B', 'name' => 'Branch B']);
        $role = Role::create(['name' => 'Supervisor', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->forceFill(['branch_id' => $branchA->id])->save();
        $user->assignRole($role);

        RoleSetting::create([
            'role_id' => $role->id,
            'can_view_all_branches' => true,
            'pos_scope' => 'all_branches',
            'is_approver' => true,
            'approval_scope' => 'all_branches',
            'receives_notifications' => false,
            'notification_scope' => 'same_branch',
        ]);

        $this->assertEqualsCanonicalizing([$branchA->id, $branchB->id], BranchAccess::userBranchIds($user));
        $this->assertEqualsCanonicalizing([$branchA->id, $branchB->id], BranchAccess::approvalBranchIds($user));
        $this->assertTrue(app(RoleSettingService::class)->canApproveBranch($user, $branchB->id));
    }

    public function test_same_branch_notification_only_returns_users_from_source_branch(): void
    {
        $branchA = BranchModel::create(['code' => 'A', 'name' => 'Branch A']);
        $branchB = BranchModel::create(['code' => 'B', 'name' => 'Branch B']);
        $role = Role::create(['name' => 'Users', 'guard_name' => 'web']);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userA->forceFill(['branch_id' => $branchA->id])->save();
        $userB->forceFill(['branch_id' => $branchB->id])->save();
        $userA->assignRole($role);
        $userB->assignRole($role);

        RoleSetting::create([
            'role_id' => $role->id,
            'can_view_all_branches' => false,
            'pos_scope' => 'same_branch',
            'is_approver' => false,
            'approval_scope' => 'same_branch',
            'receives_notifications' => true,
            'notification_scope' => 'same_branch',
        ]);

        $recipientIds = app(RoleSettingService::class)
            ->notificationRecipients($branchA->id)
            ->pluck('id')
            ->all();

        $this->assertContains($userA->id, $recipientIds);
        $this->assertNotContains($userB->id, $recipientIds);
    }

    public function test_pos_scope_is_independent_from_general_branch_access(): void
    {
        $branchA = BranchModel::create(['code' => 'A', 'name' => 'Branch A']);
        $branchB = BranchModel::create(['code' => 'B', 'name' => 'Branch B']);
        $role = Role::create(['name' => 'Kasir', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->forceFill(['branch_id' => $branchA->id])->save();
        $user->assignRole($role);

        $setting = RoleSetting::create([
            'role_id' => $role->id,
            'can_view_all_branches' => true,
            'pos_scope' => 'same_branch',
            'is_approver' => false,
            'approval_scope' => 'same_branch',
            'receives_notifications' => false,
            'notification_scope' => 'same_branch',
        ]);

        $posService = app(PenjualanPosService::class);

        $this->assertEqualsCanonicalizing([$branchA->id, $branchB->id], BranchAccess::userBranchIds($user));
        $this->assertSame([$branchA->id], $posService->transactionBranchIds($user));

        $setting->update(['pos_scope' => 'all_branches']);

        $this->assertEqualsCanonicalizing([$branchA->id, $branchB->id], $posService->transactionBranchIds($user));
    }

    public function test_stock_opname_validator_and_stock_view_access_are_independent_from_approval(): void
    {
        $branch = BranchModel::create(['code' => 'SO', 'name' => 'Branch Opname']);
        $role = Role::create(['name' => 'Validator Inventori', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->branches()->attach($branch->id);
        $user->assignRole($role);

        RoleSetting::create([
            'role_id' => $role->id,
            'can_view_all_branches' => false,
            'pos_scope' => 'same_branch',
            'is_approver' => false,
            'approval_scope' => 'same_branch',
            'is_stock_opname_validator' => true,
            'can_view_stock_during_opname' => true,
            'receives_notifications' => false,
            'notification_scope' => 'same_branch',
        ]);

        $service = app(RoleSettingService::class);

        $this->assertFalse($service->userIsApprover($user));
        $this->assertTrue($service->canValidateStockOpnameBranch($user, $branch->id));
        $this->assertTrue($service->userCanViewStockDuringOpname($user));
    }
}
