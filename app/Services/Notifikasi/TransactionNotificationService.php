<?php

namespace App\Services\Notifikasi;

use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianModel;
use App\Models\Notifikasi;
use App\Models\User;
use App\Notifications\TransactionWorkflowNotification;
use App\Services\Settings\Notification\NotificationSettingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class TransactionNotificationService
{
    public function __construct(private readonly NotificationSettingService $settings) {}

    public function isApprovalRole(?User $user): bool
    {
        return $this->settings->userIsApprover($user);
    }

    public function notifyApprovalRequest(string $module, Model $document, ?User $creator = null): void
    {
        if (! $this->settings->moduleEnabled($module)) {
            return;
        }

        $creator = $creator ?: $this->documentCreator($document);

        if (! $creator || $this->isApprovalRole($creator)) {
            return;
        }

        $snapshot = $this->documentSnapshot($module, $document);
        $approvers = $this->approversForBranch($snapshot['branch_id'] ?? null);

        if ($approvers->isEmpty()) {
            return;
        }

        $payload = array_merge($snapshot, [
            'workflow' => 'transaction_approval',
            'notification_kind' => 'approval_request',
            'title' => $snapshot['module_label'].' Menunggu Aksi',
            'message' => $snapshot['document_no'].' dibuat oleh '.$snapshot['creator_name'].' dan menunggu aksi admin/apoteker.',
            'requires_action' => true,
            'sound_enabled' => $this->settings->soundEnabled(),
        ]);

        Notification::send(
            $approvers,
            new TransactionWorkflowNotification($payload, $this->channels())
        );
    }

    public function notifyActionResult(string $module, Model $document, string $action, ?User $actor = null): void
    {
        if (! $this->settings->moduleEnabled($module)) {
            return;
        }

        $this->markApprovalRequestsHandled($module, (int) $document->getKey());

        if (! $this->settings->notifyCreatorEnabled()) {
            return;
        }

        $creator = $this->documentCreator($document);

        if (! $creator || $this->isApprovalRole($creator)) {
            return;
        }

        $actor = $actor ?: Auth::user();
        $snapshot = $this->documentSnapshot($module, $document);
        $actionMeta = $this->actionMeta($module, $action);

        $payload = array_merge($snapshot, [
            'workflow' => 'transaction_approval',
            'notification_kind' => 'approval_result',
            'title' => $snapshot['module_label'].' '.$actionMeta['label'],
            'message' => $snapshot['document_no'].' '.$actionMeta['message'].' oleh '.($actor->name ?? 'Admin/Apoteker').'.',
            'requires_action' => false,
            'action_key' => $action,
            'action_label' => $actionMeta['label'],
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
            'acted_at' => now()->toDateTimeString(),
            'sound_enabled' => $this->settings->soundEnabled(),
        ]);

        $creator->notify(new TransactionWorkflowNotification($payload, $this->channels()));
    }

    public function currentState(array $data): array
    {
        $module = $data['module'] ?? null;
        $documentId = $data['document_id'] ?? null;

        if (! $module || ! $documentId) {
            return [
                'exists' => false,
                'status' => $data['status'] ?? null,
                'status_label' => $data['status_label'] ?? 'Tidak diketahui',
                'status_tone' => 'muted',
                'action_open' => false,
            ];
        }

        $document = match ($module) {
            'pembelian' => PembelianModel::find($documentId),
            'penerimaan' => PenerimaanBarangModel::find($documentId),
            'retur_pembelian' => ReturPembelianModel::find($documentId),
            default => null,
        };

        if (! $document) {
            return [
                'exists' => false,
                'status' => 'missing',
                'status_label' => 'Dokumen hilang',
                'status_tone' => 'danger',
                'action_open' => false,
            ];
        }

        $status = (string) $document->status;
        $statusMeta = $this->statusMeta($module, $status);

        return [
            'exists' => true,
            'status' => $status,
            'status_label' => $statusMeta['label'],
            'status_tone' => $statusMeta['tone'],
            'action_open' => $this->statusAllowsAction($module, $status),
        ];
    }

    public function actionButtons(array $data, ?User $user = null): array
    {
        $user = $user ?: Auth::user();
        $state = $this->currentState($data);

        if (
            ! $this->isApprovalRole($user)
            || ($data['notification_kind'] ?? null) !== 'approval_request'
            || ! $state['action_open']
        ) {
            return [];
        }

        return $data['actions'] ?? [];
    }

    private function approversForBranch(?int $branchId)
    {
        $roleKeys = $this->settings->approverRoleKeys();

        if (empty($roleKeys)) {
            return collect();
        }

        $query = User::whereHas('roles', function ($roleQuery) use ($roleKeys) {
            $roleQuery->where(function ($nested) use ($roleKeys) {
                foreach ($roleKeys as $role) {
                    $nested->orWhereRaw('LOWER(name) = ?', [$role]);
                }
            });
        });

        if ($branchId && $this->settings->sameBranchOnly()) {
            $usersTable = (new User)->getTable();
            $hasDirectBranchColumn = Schema::hasColumn($usersTable, 'branch_id');

            $query->where(function ($userQuery) use ($branchId, $hasDirectBranchColumn, $usersTable) {
                if ($hasDirectBranchColumn) {
                    $userQuery->where($usersTable.'.branch_id', $branchId)
                        ->orWhereHas('branches', fn ($branchQuery) => $branchQuery->where('branches.id', $branchId));

                    return;
                }

                $userQuery->whereHas('branches', fn ($branchQuery) => $branchQuery->where('branches.id', $branchId));
            });
        }

        return $query->get();
    }

    private function markApprovalRequestsHandled(string $module, int $documentId): void
    {
        Notifikasi::where('type', TransactionWorkflowNotification::class)
            ->whereNull('read_at')
            ->get()
            ->filter(function (Notifikasi $notification) use ($module, $documentId) {
                $data = $notification->data ?: [];

                return ($data['workflow'] ?? null) === 'transaction_approval'
                    && ($data['notification_kind'] ?? null) === 'approval_request'
                    && ($data['module'] ?? null) === $module
                    && (int) ($data['document_id'] ?? 0) === $documentId;
            })
            ->each(fn (Notifikasi $notification) => $notification->update(['read_at' => now()]));
    }

    private function documentSnapshot(string $module, Model $document): array
    {
        return match ($module) {
            'pembelian' => $this->purchaseSnapshot($document),
            'penerimaan' => $this->receiptSnapshot($document),
            'retur_pembelian' => $this->returnSnapshot($document),
            default => [],
        };
    }

    private function purchaseSnapshot(PembelianModel $po): array
    {
        $po->load(['branch', 'distributor', 'createdBy', 'details.obat.satuan', 'details.satuanKonversi.satuan']);
        $statusMeta = $this->statusMeta('pembelian', (string) $po->status);

        return [
            'module' => 'pembelian',
            'module_label' => 'Pembelian',
            'module_icon' => 'mdi-shopping-outline',
            'document_id' => $po->id,
            'document_no' => $po->no_po,
            'reference_no' => $po->no_po,
            'no_po' => $po->no_po,
            'document_date' => $po->tanggal_po,
            'amount' => (float) $po->total_estimasi,
            'item_count' => $po->details->count(),
            'items' => $this->purchaseItems($po),
            'supplier' => $po->distributor->nama ?? '-',
            'branch_id' => $po->branch_id,
            'branch_name' => $po->branch->name ?? '-',
            'creator_id' => $po->created_by,
            'creator_name' => $po->createdBy->name ?? '-',
            'status' => $po->status,
            'status_label' => $statusMeta['label'],
            'status_tone' => $statusMeta['tone'],
            'page_url' => route('pembelian.pembelian'),
            'url' => route('pembelian.pembelian'),
            'detail_url' => route('pembelian.show', $po->id),
            'actions' => [
                [
                    'key' => 'approve',
                    'label' => 'Setujui',
                    'method' => 'PUT',
                    'url' => route('pembelian.approve', $po->id),
                    'icon' => 'mdi-check-circle-outline',
                    'tone' => 'success',
                ],
                [
                    'key' => 'reject',
                    'label' => 'Tolak',
                    'method' => 'PUT',
                    'url' => route('pembelian.reject', $po->id),
                    'icon' => 'mdi-close-circle-outline',
                    'tone' => 'danger',
                ],
            ],
        ];
    }

    private function receiptSnapshot(PenerimaanBarangModel $penerimaan): array
    {
        $penerimaan->load([
            'purchaseOrder.branch',
            'purchaseOrder',
            'distributor',
            'createdBy',
            'details.obat.satuan',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
        ]);
        $statusMeta = $this->statusMeta('penerimaan', (string) $penerimaan->status);

        return [
            'module' => 'penerimaan',
            'module_label' => 'Penerimaan',
            'module_icon' => 'mdi-package-variant-closed-check',
            'document_id' => $penerimaan->id,
            'document_no' => $penerimaan->nomor_penerimaan,
            'reference_no' => $penerimaan->purchaseOrder->no_po ?? '-',
            'no_po' => $penerimaan->purchaseOrder->no_po ?? '-',
            'document_date' => optional($penerimaan->tanggal_penerimaan)->format('Y-m-d'),
            'amount' => (float) $penerimaan->grand_total,
            'item_count' => (int) ($penerimaan->total_barang ?: $penerimaan->details->count()),
            'items' => $this->receiptItems($penerimaan),
            'supplier' => $penerimaan->distributor->nama ?? '-',
            'branch_id' => $penerimaan->purchaseOrder->branch_id ?? null,
            'branch_name' => $penerimaan->purchaseOrder->branch->name ?? '-',
            'creator_id' => $penerimaan->created_by,
            'creator_name' => $penerimaan->createdBy->name ?? '-',
            'status' => $penerimaan->status,
            'status_label' => $statusMeta['label'],
            'status_tone' => $statusMeta['tone'],
            'page_url' => route('penerimaan.penerimaan'),
            'url' => route('penerimaan.penerimaan'),
            'detail_url' => route('penerimaan.show', $penerimaan->id),
            'actions' => [
                [
                    'key' => 'post',
                    'label' => 'Posting',
                    'method' => 'PUT',
                    'url' => route('penerimaan.post', $penerimaan->id),
                    'icon' => 'mdi-send-check-outline',
                    'tone' => 'success',
                ],
                [
                    'key' => 'cancel',
                    'label' => 'Batalkan',
                    'method' => 'PUT',
                    'url' => route('penerimaan.cancel', $penerimaan->id),
                    'icon' => 'mdi-cancel',
                    'tone' => 'danger',
                ],
            ],
        ];
    }

    private function returnSnapshot(ReturPembelianModel $retur): array
    {
        $retur->load([
            'purchaseOrder.branch',
            'penerimaanBarang',
            'distributor',
            'createdBy',
            'details.obat.satuan',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
        ]);
        $statusMeta = $this->statusMeta('retur_pembelian', (string) $retur->status);

        return [
            'module' => 'retur_pembelian',
            'module_label' => 'Retur Pembelian',
            'module_icon' => 'mdi-backup-restore',
            'document_id' => $retur->id,
            'document_no' => $retur->nomor_retur,
            'reference_no' => $retur->penerimaanBarang->nomor_penerimaan ?? ($retur->purchaseOrder->no_po ?? '-'),
            'no_po' => $retur->purchaseOrder->no_po ?? '-',
            'document_date' => optional($retur->tanggal_retur)->format('Y-m-d'),
            'amount' => (float) $retur->grand_total,
            'item_count' => (int) ($retur->total_barang ?: $retur->details->count()),
            'items' => $this->returnItems($retur),
            'supplier' => $retur->distributor->nama ?? '-',
            'branch_id' => $retur->purchaseOrder->branch_id ?? null,
            'branch_name' => $retur->purchaseOrder->branch->name ?? '-',
            'creator_id' => $retur->created_by,
            'creator_name' => $retur->createdBy->name ?? '-',
            'status' => $retur->status,
            'status_label' => $statusMeta['label'],
            'status_tone' => $statusMeta['tone'],
            'page_url' => route('returPembelian.returPembelian'),
            'url' => route('returPembelian.returPembelian'),
            'detail_url' => route('returPembelian.show', $retur->id),
            'actions' => [
                [
                    'key' => 'post',
                    'label' => 'Posting',
                    'method' => 'PUT',
                    'url' => route('returPembelian.post', $retur->id),
                    'icon' => 'mdi-send-check-outline',
                    'tone' => 'success',
                ],
                [
                    'key' => 'cancel',
                    'label' => 'Batalkan',
                    'method' => 'PUT',
                    'url' => route('returPembelian.cancel', $retur->id),
                    'icon' => 'mdi-cancel',
                    'tone' => 'danger',
                ],
            ],
        ];
    }

    private function purchaseItems(PembelianModel $po): array
    {
        return $po->details
            ->map(function (PembelianDetailModel $detail, int $index) {
                $unit = $detail->satuanKonversi?->satuan?->nama ?? ($detail->obat?->satuan?->nama ?? 'satuan');
                $stockUnit = $detail->obat?->satuan?->nama ?? null;
                $conversion = max(1, (float) ($detail->satuanKonversi?->konversi ?? 1));
                $qty = (float) $detail->qty;
                $price = (float) $detail->harga_estimasi;
                $total = (float) ($detail->subtotal ?: ($qty * $price));

                return [
                    'row_no' => $index + 1,
                    'obat_id' => $detail->obat_id,
                    'kode_obat' => $detail->obat?->kode_obat ?? '-',
                    'nama_obat' => $detail->obat?->nama_obat ?? '-',
                    'qty' => $qty,
                    'unit' => $unit,
                    'stock_qty' => $conversion > 1 ? $qty * $conversion : null,
                    'stock_unit' => $conversion > 1 ? $stockUnit : null,
                    'unit_conversion' => $conversion,
                    'price' => $price,
                    'subtotal' => $total,
                    'discount' => null,
                    'tax' => null,
                    'total' => $total,
                    'no_batch' => null,
                    'expired_date' => null,
                    'note' => null,
                ];
            })
            ->values()
            ->all();
    }

    private function receiptItems(PenerimaanBarangModel $penerimaan): array
    {
        return $penerimaan->details
            ->map(function (PenerimaanBarangDetailModel $detail, int $index) {
                $conversion = $this->detailConversionFactor($detail);
                $qty = (float) $detail->qty_diterima;
                $stockQty = (float) ($detail->qty_diterima_stok ?: ($qty * $conversion));
                $price = (float) $detail->harga_beli;
                $subtotal = (float) ($detail->subtotal ?: ($qty * $price));

                return [
                    'row_no' => $index + 1,
                    'obat_id' => $detail->obat_id,
                    'kode_obat' => $detail->obat?->kode_obat ?? '-',
                    'nama_obat' => $detail->obat?->nama_obat ?? '-',
                    'qty' => $qty,
                    'unit' => $this->purchaseUnitLabel($detail),
                    'stock_qty' => $stockQty,
                    'stock_unit' => $this->stockUnitLabel($detail),
                    'unit_conversion' => $conversion,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'discount' => (float) ($detail->diskon ?? 0),
                    'tax' => (float) ($detail->ppn ?? 0),
                    'total' => (float) ($detail->total ?: $subtotal),
                    'no_batch' => $detail->no_batch,
                    'expired_date' => $this->dateValue($detail->expired_date),
                    'note' => null,
                ];
            })
            ->values()
            ->all();
    }

    private function returnItems(ReturPembelianModel $retur): array
    {
        return $retur->details
            ->map(function (ReturPembelianDetailModel $detail, int $index) {
                $conversion = $this->detailConversionFactor($detail);
                $qty = (float) $detail->qty_retur;
                $stockQty = (float) ($detail->qty_retur_stok ?: ($qty * $conversion));
                $price = (float) $detail->harga_beli;
                $subtotal = (float) ($detail->subtotal ?: ($qty * $price));

                return [
                    'row_no' => $index + 1,
                    'obat_id' => $detail->obat_id,
                    'kode_obat' => $detail->obat?->kode_obat ?? '-',
                    'nama_obat' => $detail->obat?->nama_obat ?? '-',
                    'qty' => $qty,
                    'unit' => $this->purchaseUnitLabel($detail),
                    'stock_qty' => $stockQty,
                    'stock_unit' => $this->stockUnitLabel($detail),
                    'unit_conversion' => $conversion,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'discount' => (float) ($detail->diskon ?? 0),
                    'tax' => (float) ($detail->ppn ?? 0),
                    'total' => (float) ($detail->total ?: $subtotal),
                    'no_batch' => $detail->no_batch,
                    'expired_date' => $this->dateValue($detail->expired_date),
                    'note' => $detail->alasan_item,
                ];
            })
            ->values()
            ->all();
    }

    private function detailConversionFactor(PenerimaanBarangDetailModel|ReturPembelianDetailModel $detail): float
    {
        $storedConversion = (float) ($detail->konversi_satuan ?? 0);

        if ($storedConversion > 0) {
            return max(1, $storedConversion);
        }

        return max(1, (float) ($detail->purchaseOrderDetail?->satuanKonversi?->konversi ?? 1));
    }

    private function purchaseUnitLabel(PenerimaanBarangDetailModel|ReturPembelianDetailModel $detail): string
    {
        return $detail->satuan_beli
            ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama ?? ($detail->obat?->satuan?->nama ?? 'satuan'));
    }

    private function stockUnitLabel(PenerimaanBarangDetailModel|ReturPembelianDetailModel $detail): string
    {
        return $detail->satuan_stok ?: ($detail->obat?->satuan?->nama ?? 'PCS');
    }

    private function dateValue($date): ?string
    {
        if (! $date) {
            return null;
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return (string) $date;
    }

    private function documentCreator(Model $document): ?User
    {
        if (method_exists($document, 'createdBy')) {
            $document->loadMissing('createdBy');

            return $document->createdBy;
        }

        return null;
    }

    private function channels(): array
    {
        return $this->settings->broadcastEnabled()
            ? ['database', 'broadcast']
            : ['database'];
    }

    private function statusAllowsAction(string $module, string $status): bool
    {
        return match ($module) {
            'pembelian' => in_array($status, ['draft', 'waiting_approval'], true),
            'penerimaan', 'retur_pembelian' => $status === 'draft',
            default => false,
        };
    }

    private function statusMeta(string $module, string $status): array
    {
        return match ($status) {
            'draft' => ['label' => 'Draft', 'tone' => 'warning'],
            'waiting_approval' => ['label' => 'Menunggu Aksi', 'tone' => 'warning'],
            'approved' => ['label' => 'Disetujui', 'tone' => 'success'],
            'rejected' => ['label' => 'Ditolak', 'tone' => 'danger'],
            'posted' => ['label' => 'Posted', 'tone' => 'success'],
            'cancelled' => ['label' => 'Dibatalkan', 'tone' => 'danger'],
            'diterima_sebagian' => ['label' => 'Diterima Sebagian', 'tone' => 'info'],
            'selesai' => ['label' => 'Selesai', 'tone' => 'success'],
            default => ['label' => ucfirst(str_replace('_', ' ', $status)), 'tone' => 'muted'],
        };
    }

    private function actionMeta(string $module, string $action): array
    {
        return match ($action) {
            'approved' => ['label' => 'Disetujui', 'message' => 'telah disetujui'],
            'rejected' => ['label' => 'Ditolak', 'message' => 'telah ditolak'],
            'posted' => ['label' => 'Diposting', 'message' => 'telah diposting'],
            'cancelled' => ['label' => 'Dibatalkan', 'message' => 'telah dibatalkan'],
            default => ['label' => ucfirst($action), 'message' => 'telah diproses'],
        };
    }
}
