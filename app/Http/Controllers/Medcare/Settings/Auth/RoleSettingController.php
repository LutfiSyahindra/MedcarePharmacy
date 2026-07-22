<?php

namespace App\Http\Controllers\Medcare\Settings\Auth;

use App\Http\Controllers\Controller;
use App\Services\Settings\Auth\RoleSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSettingController extends Controller
{
    public function __construct(private readonly RoleSettingService $roleSettings) {}

    public function index()
    {
        abort_unless($this->roleSettings->userCanManage(Auth::user()), 403);

        return view('medcare.settings.auth.roleSetting.roleSetting', [
            'roles' => $this->roleSettings->rolesWithSettings(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($this->roleSettings->userCanManage(Auth::user()), 403);

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.role_id' => ['required', 'integer', 'distinct', 'exists:roles,id'],
            'settings.*.can_view_all_branches' => ['nullable', 'boolean'],
            'settings.*.pos_scope' => ['nullable', 'in:same_branch,all_branches'],
            'settings.*.is_approver' => ['nullable', 'boolean'],
            'settings.*.approval_scope' => ['nullable', 'in:same_branch,all_branches'],
            'settings.*.receives_notifications' => ['nullable', 'boolean'],
            'settings.*.notification_scope' => ['nullable', 'in:same_branch,all_branches'],
        ]);

        $roles = $this->roleSettings->update($validated['settings']);

        return response()->json([
            'status' => 'success',
            'message' => 'Konfigurasi role berhasil disimpan.',
            'summary' => [
                'all_branch' => $roles->where('can_view_all_branches', true)->count(),
                'pos_all_branch' => $roles->where('pos_scope', RoleSettingService::ALL_BRANCHES)->count(),
                'approver' => $roles->where('is_approver', true)->count(),
                'notification' => $roles->where('receives_notifications', true)->count(),
            ],
        ]);
    }
}
