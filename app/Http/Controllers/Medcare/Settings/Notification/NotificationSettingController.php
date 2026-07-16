<?php

namespace App\Http\Controllers\Medcare\Settings\Notification;

use App\Http\Controllers\Controller;
use App\Services\Settings\Notification\NotificationSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationSettingController extends Controller
{
    public function __construct(private readonly NotificationSettingService $settings) {}

    public function index()
    {
        abort_unless($this->settings->userCanManage(Auth::user()), 403);

        return view('medcare.settings.notifikasi.notifikasi', [
            'settings' => $this->settings->settings(),
            'modules' => NotificationSettingService::MODULES,
            'stats' => $this->settings->stats(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($this->settings->userCanManage(Auth::user()), 403);

        $request->validate([
            'navbar_limit' => ['nullable', 'integer', 'min:3', 'max:20'],
        ]);

        $settings = $this->settings->update([
            'modules' => [
                'pembelian' => ['enabled' => $request->boolean('modules.pembelian.enabled')],
                'penerimaan' => ['enabled' => $request->boolean('modules.penerimaan.enabled')],
                'retur_pembelian' => ['enabled' => $request->boolean('modules.retur_pembelian.enabled')],
            ],
            'roles' => [
                'admin' => $request->boolean('roles.admin'),
                'apoteker' => $request->boolean('roles.apoteker'),
            ],
            'notify_creator' => $request->boolean('notify_creator'),
            'broadcast' => $request->boolean('broadcast'),
            'sound' => $request->boolean('sound'),
            'same_branch_only' => $request->boolean('same_branch_only'),
            'navbar_limit' => $request->integer('navbar_limit', 8),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Konfigurasi notifikasi berhasil diperbarui.',
            'settings' => $settings,
        ]);
    }
}
