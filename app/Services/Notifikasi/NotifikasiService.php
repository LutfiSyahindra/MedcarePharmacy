<?php

namespace App\Services\Notifikasi;

use App\Repositories\Notifikasi\NotifikasiRepository;

class NotifikasiService
{
    protected $NotifikasiRepository;

    public function __construct(NotifikasiRepository $NotifikasiRepository)
    {
        $this->NotifikasiRepository = $NotifikasiRepository;
    }
    public function getNotifikasi()
    {
        $Notifikasi = $this->NotifikasiRepository->getNotifikasi();

        $dataNotifikasi = [];
        foreach ($Notifikasi as $n) {
            $dataNotifikasi[] = [
                'id'        => $n->id,
                'title'     => $n->title,
                'message'   => $n->message,
                'no_po'     => $n->no_po ?? '-',
                'waktu'     => $n->created_at->diffForHumans(),
                'status'    => $n->isUnread()
                                ? '<span class="badge bg-warning">Belum</span>'
                                : '<span class="badge bg-success">Dibaca</span>',
                'is_unread' => $n->isUnread(),
                'url'       => $n->url,
            ];
        }

        return $dataNotifikasi;
    }

    public function markAsRead(string $id): void
    {
        $notif = $this->NotifikasiRepository->findById($id);

        if ($notif && $notif->isUnread()) {
            $notif->update(['read_at' => now()]);
        }
    }
}
