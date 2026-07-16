<?php

namespace App\Repositories\Notifikasi;

use App\Models\Notifikasi;
use App\Models\User;
use App\Services\Settings\Notification\NotificationSettingService;
use Illuminate\Support\Facades\Auth;

class NotifikasiRepository
{
    public function __construct(private readonly NotificationSettingService $settings) {}

    public function getNotifikasi()
    {
        return Notifikasi::where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::user()->id)
            ->latest()
            ->get();
    }

    public function latestUnread()
    {
        return Notifikasi::where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::user()->id)
            ->whereNull('read_at')
            ->latest()
            ->limit($this->settings->navbarLimit())
            ->get();
    }

    public function findById(string $id): ?Notifikasi
    {
        return Notifikasi::where('id', $id)
            ->where('notifiable_id', Auth::user()->id)
            ->first();
    }
}
