<?php

namespace App\Repositories\Notifikasi;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Mockery\Matcher\Not;

class NotifikasiRepository
{
    public function getNotifikasi()
    {
        return Notifikasi::where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::user()->id)
            ->latest()
            ->get();
    }

    public function findById(string $id): ?Notifikasi
    {
        return Notifikasi::where('id', $id)
            ->where('notifiable_id', Auth::user()->id)
            ->first();
    }

}
