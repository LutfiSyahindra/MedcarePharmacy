<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\RoleSetting;
use App\Models\User;
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
                        'is_approver' => true,
                        'approval_scope' => 'all_branches',
                        'receives_notifications' => false,
                        'notification_scope' => 'same_branch',
                    ],
                    [
                        'role_id' => $userRole->id,
                        'can_view_all_branches' => false,
                        'is_approver' => false,
                        'approval_scope' => 'same_branch',
                        'receives_notifications' => true,
                        'notification_scope' => 'same_branch',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('summary.approver', 1)
            ->assertJsonPath('summary.notification', 1);

        $this->assertDatabaseHas('role_settings', [
            'role_id' => $userRole->id,
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
}
