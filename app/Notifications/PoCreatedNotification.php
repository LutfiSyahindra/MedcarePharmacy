<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;

class PoCreatedNotification extends Notification
{
    public function __construct(public $po) {}

    public function via($notifiable)
    {
        // realtime + simpan
        return ['database','broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title'   => 'PO Baru',
            'message' => $this->po->no_po.' menunggu persetujuan',
            'no_po'   => $this->po->no_po, // ⬅️ penting untuk tabel notifikasi
            'url'     => route('pembelian.show', $this->po->id),
        ];
    }

    public function toBroadcast($notifiable)
    {
        Log::info('🔥 BROADCAST PO CREATED DIKIRIM', [
            'user' => $notifiable->id,
            'po' => $this->po->no_po,
        ]);
        return new BroadcastMessage([
            'title'   => 'PO Baru',
            'message' => $this->po->no_po.' menunggu persetujuan',
            'no_po'   => $this->po->no_po,
            'url'     => route('pembelian.show', $this->po->id),
        ]);
    }
}
