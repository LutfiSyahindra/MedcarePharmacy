<?php

namespace App\Services\Notifikasi;

use App\Models\Notifikasi;
use App\Repositories\Notifikasi\NotifikasiRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotifikasiService
{
    protected $NotifikasiRepository;

    public function __construct(
        NotifikasiRepository $NotifikasiRepository,
        private readonly TransactionNotificationService $transactionNotifications
    ) {
        $this->NotifikasiRepository = $NotifikasiRepository;
    }

    public function getNotifikasi()
    {
        $Notifikasi = $this->NotifikasiRepository->getNotifikasi();

        return $Notifikasi
            ->map(fn (Notifikasi $notification) => $this->formatForTable($notification))
            ->all();
    }

    public function latestUnread(): array
    {
        return $this->NotifikasiRepository
            ->latestUnread()
            ->map(fn (Notifikasi $notification) => $this->formatForNavbar($notification))
            ->all();
    }

    public function summaryForUser(): array
    {
        $notifications = $this->NotifikasiRepository->getNotifikasi();
        $pendingAction = 0;
        $resultCount = 0;

        foreach ($notifications as $notification) {
            $formatted = $this->format($notification);

            if ($formatted['can_action']) {
                $pendingAction++;
            }

            if ($formatted['notification_kind'] === 'approval_result') {
                $resultCount++;
            }
        }

        return [
            'total' => $notifications->count(),
            'unread' => $notifications->whereNull('read_at')->count(),
            'pending_action' => $pendingAction,
            'results' => $resultCount,
        ];
    }

    public function markAsRead(string $id): ?array
    {
        $notif = $this->NotifikasiRepository->findById($id);

        if ($notif && $notif->isUnread()) {
            $notif->update(['read_at' => now()]);
            $notif->refresh();
        }

        return $notif ? $this->format($notif) : null;
    }

    public function format(Notifikasi $notification): array
    {
        $data = $notification->data ?: [];
        $state = $this->transactionNotifications->currentState($data);
        $actionButtons = $this->transactionNotifications->actionButtons($data, Auth::user());
        $statusLabel = $state['status_label'] ?? ($data['status_label'] ?? 'Tidak diketahui');
        $statusTone = $state['status_tone'] ?? ($data['status_tone'] ?? 'muted');
        $documentNo = $data['document_no'] ?? $data['no_po'] ?? '-';
        $moduleLabel = $data['module_label'] ?? 'Notifikasi';
        $notificationKind = $data['notification_kind'] ?? ($data['type'] ?? 'general');
        $items = collect($data['items'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->values()
            ->all();

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'Notifikasi',
            'message' => $data['message'] ?? '',
            'module' => $data['module'] ?? null,
            'module_label' => $moduleLabel,
            'module_icon' => $data['module_icon'] ?? 'mdi-bell-outline',
            'notification_kind' => $notificationKind,
            'kind_label' => $notificationKind === 'approval_result' ? 'Hasil Aksi' : 'Perlu Aksi',
            'document_id' => $data['document_id'] ?? null,
            'document_no' => $documentNo,
            'reference_no' => $data['reference_no'] ?? $data['no_po'] ?? '-',
            'no_po' => $data['no_po'] ?? '-',
            'supplier' => $data['supplier'] ?? '-',
            'branch_name' => $data['branch_name'] ?? '-',
            'creator_name' => $data['creator_name'] ?? '-',
            'actor_name' => $data['actor_name'] ?? null,
            'document_date' => $data['document_date'] ?? '-',
            'amount' => (float) ($data['amount'] ?? 0),
            'item_count' => (int) ($data['item_count'] ?? 0),
            'items' => $items,
            'items_summary' => $this->itemsSummary($items),
            'url' => $data['url'] ?? $data['page_url'] ?? '#',
            'page_url' => $data['page_url'] ?? $data['url'] ?? '#',
            'detail_url' => $data['detail_url'] ?? null,
            'time' => $notification->created_at?->diffForHumans() ?? '-',
            'created_at' => optional($notification->created_at)->format('Y-m-d H:i:s'),
            'is_unread' => $notification->isUnread(),
            'read_label' => $notification->isUnread() ? 'Belum dibaca' : 'Dibaca',
            'status' => $state['status'] ?? ($data['status'] ?? null),
            'status_label' => $statusLabel,
            'status_tone' => $statusTone,
            'requires_action' => (bool) ($data['requires_action'] ?? false),
            'can_action' => ! empty($actionButtons),
            'action_buttons' => $actionButtons,
            'payload' => $data,
        ];
    }

    private function formatForTable(Notifikasi $notification): array
    {
        $formatted = $this->format($notification);

        return $formatted + [
            'document' => $this->documentHtml($formatted),
            'summary' => $this->summaryHtml($formatted),
            'status_badge' => $this->statusHtml($formatted),
            'waktu' => $formatted['time'],
            'actions' => $this->actionsHtml($formatted),
        ];
    }

    private function formatForNavbar(Notifikasi $notification): array
    {
        $formatted = $this->format($notification);

        return [
            'id' => $formatted['id'],
            'title' => $formatted['title'],
            'message' => Str::limit(strip_tags($formatted['message']), 84),
            'url' => $formatted['page_url'],
            'time' => $formatted['time'],
            'module_label' => $formatted['module_label'],
            'module_icon' => $formatted['module_icon'],
            'document_no' => $formatted['document_no'],
            'items_summary' => Str::limit($formatted['items_summary'], 110),
            'status_label' => $formatted['status_label'],
            'status_tone' => $formatted['status_tone'],
            'can_action' => $formatted['can_action'],
        ];
    }

    private function itemsSummary(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $names = collect($items)
            ->map(function (array $item) {
                $name = $item['nama_obat'] ?? '-';
                $qty = array_key_exists('qty', $item) ? $this->formatQuantity($item['qty']) : '';
                $unit = trim((string) ($item['unit'] ?? ''));
                $quantity = trim($qty.' '.$unit);

                return $quantity !== ''
                    ? $name.' ('.$quantity.')'
                    : $name;
            })
            ->filter()
            ->take(3)
            ->values();

        $remaining = count($items) - $names->count();
        $summary = $names->implode(', ');

        return $remaining > 0
            ? $summary.', +'.$remaining.' item'
            : $summary;
    }

    private function formatQuantity($value): string
    {
        $formatted = number_format((float) $value, 2, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }

    private function documentHtml(array $notification): string
    {
        return '
            <div class="notif-document">
                <span class="notif-document-icon"><i class="mdi '.e($notification['module_icon']).'"></i></span>
                <div>
                    <strong>'.e($notification['document_no']).'</strong>
                    <small>'.e($notification['module_label']).' · Ref '.e($notification['reference_no']).'</small>
                </div>
            </div>
        ';
    }

    private function summaryHtml(array $notification): string
    {
        $itemsLine = $notification['items_summary'] !== ''
            ? '<small class="notif-items-line">Isi: '.e($notification['items_summary']).'</small>'
            : '';

        return '
            <div class="notif-summary">
                <strong>'.e($notification['title']).'</strong>
                <span>'.e($notification['message']).'</span>
                '.$itemsLine.'
                <small>'.e($notification['supplier']).' · '.e($notification['branch_name']).' · '.number_format($notification['item_count'], 0, ',', '.').' item</small>
            </div>
        ';
    }

    private function statusHtml(array $notification): string
    {
        $readClass = $notification['is_unread'] ? 'is-unread' : 'is-read';
        $actionClass = $notification['can_action'] ? 'is-actionable' : 'is-passive';

        return '
            <div class="notif-status-stack">
                <span class="notif-state is-'.e($notification['status_tone']).'">'.e($notification['status_label']).'</span>
                <span class="notif-read '.$readClass.'">'.e($notification['read_label']).'</span>
                <span class="notif-action-state '.$actionClass.'">'.($notification['can_action'] ? 'Perlu aksi' : e($notification['kind_label'])).'</span>
            </div>
        ';
    }

    private function actionsHtml(array $notification): string
    {
        return '
            <div class="notif-table-actions">
                <button type="button" class="notif-action-btn is-primary" onclick="openNotification(\''.e($notification['id']).'\')" title="Buka notifikasi">
                    <i class="mdi mdi-eye-outline"></i>
                </button>
                <a class="notif-action-btn" href="'.e($notification['page_url']).'" title="Buka halaman">
                    <i class="mdi mdi-open-in-new"></i>
                </a>
            </div>
        ';
    }
}
