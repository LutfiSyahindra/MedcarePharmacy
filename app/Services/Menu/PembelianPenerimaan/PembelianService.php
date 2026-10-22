<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Repositories\Menu\PembelianPenerimaan\PembelianRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembelianService
{
    protected $PembelianRepository;

    public function __construct(
        PembelianRepository $PembelianRepository,
        private readonly PurchaseReceiptNumberService $numbering,
    ) {
        $this->PembelianRepository = $PembelianRepository;
    }

    /**
     * Create a new class instance.
     */
    public function getPembelian(?array $branchIds = null)
    {
        $dataPembelian = $this->PembelianRepository->getPembelian($branchIds);

        return $dataPembelian;
    }

    public function generatePo()
    {
        return $this->numbering->previewPurchaseOrder();
    }

    public function reservePoNumber(): string
    {
        return $this->numbering->nextPurchaseOrder();
    }

    public function validateManualPoNumber(string $number, ?int $ignoreId = null): void
    {
        $this->numbering->validateManualPurchaseOrder($number, $ignoreId);
    }

    public function getPembelianTable(?array $branchIds = null, ?string $medicineSearch = null, ?int $medicineId = null, ?string $dateStart = null, ?string $dateEnd = null)
    {
        $Pembelian = $this->PembelianRepository->getPembelian($branchIds, $medicineSearch, $medicineId, $dateStart, $dateEnd)->load([
            'distributor',
            'createdBy',
            'approvedBy',
            'branch',
        ]);
        $Pembelian->loadCount('details')->loadSum('details', 'qty');
        $Pembelian->loadExists([
            'penerimaanBarang as has_receipt',
            'penerimaanBarang as has_non_draft_receipt' => fn ($query) => $query
                ->where(fn ($receipts) => $receipts->where('status', '!=', 'draft')->orWhereNotNull('posted_at')),
            'penerimaanBarang as has_draft_receipt' => fn ($query) => $query->where('status', 'draft'),
        ]);

        $dataPembelian = [];
        foreach ($Pembelian as $r) {
            $dataPembelian[] = [
                'id' => $r->id,
                'no_po' => $r->no_po,
                'branch_key' => (int) $r->branch_id,
                'branch_id' => $r->branch->name ?? '-',
                'distributor_id' => $r->distributor->nama ?? '-',
                'tanggal_po' => $r->tanggal_po ?? '-',
                'item_count' => (int) ($r->details_count ?? 0),
                'total_qty' => (float) ($r->details_sum_qty ?? 0),
                'matched_medicines' => ($medicineId !== null || trim($medicineSearch ?? '') !== '') && $r->relationLoaded('details')
                    ? $r->details->map(fn ($detail) => [
                        'obat_id' => (int) $detail->obat_id,
                        'kode_obat' => $detail->obat->kode_obat,
                        'nama_obat' => $detail->obat->nama_obat,
                        'qty' => (float) $detail->qty,
                        'satuan' => $detail->satuanKonversi?->satuan?->nama ?? $detail->obat->satuan?->nama ?? '-',
                    ])->values()->all()
                    : [],
                'total_estimasi' => $r->total_estimasi ?? '-',
                'status' => $r->has_draft_receipt && in_array($r->status, ['approved', 'diterima_sebagian'], true)
                    ? 'dalam_penerimaan' : ($r->status ?? '-'),
                'purchase_order_status' => $r->status,
                'has_receipt' => (bool) $r->has_receipt,
                'can_edit' => $r->canEdit(),
                'can_delete' => $r->canDelete(),
                'can_reopen_approval' => $r->canReopenApproval(),
                'catatan' => $r->catatan ?? '-',
                'created_by' => $r->createdBy->name ?? '-',
                'approved_by' => $r->approvedBy->name ?? null,
            ];
        }

        return $dataPembelian;
    }

    public function createPembelian(array $data)
    {
        $dataPembelian = $this->PembelianRepository->createPembelian($data);

        return $dataPembelian;
    }

    public function createPembelianDetail(array $data)
    {
        $dataPembelianDetail = $this->PembelianRepository->createPembelianDetail($data);

        return $dataPembelianDetail;
    }

    public function findByIdPembelian($id, ?array $branchIds = null, bool $lockForUpdate = false)
    {
        $Pembelian = $this->PembelianRepository->findByIdPembelian($id, $branchIds, $lockForUpdate);

        return $Pembelian;
    }

    public function DetailPembelian($id, ?array $branchIds = null)
    {
        return $this->PembelianRepository->DetailPembelian($id, $branchIds);
    }

    public function updatePembelian($id, array $data, ?array $branchIds = null)
    {
        $Pembelian = $this->PembelianRepository->findByIdPembelian($id, $branchIds);
        $Pembelian->update($data);

        return $Pembelian;
    }

    public function updateStatus($id, $status, $approvedBy = null, ?array $branchIds = null)
    {
        return DB::transaction(function () use ($id, $status, $approvedBy, $branchIds) {
            $purchaseOrder = $this->PembelianRepository->findByIdPembelian($id, $branchIds, true);
            abort_if(! $purchaseOrder, 404, 'Purchase order tidak ditemukan.');

            $allowed = $status === 'waiting_approval'
                ? $purchaseOrder->canReopenApproval()
                : in_array($status, ['approved', 'rejected'], true)
                    && $purchaseOrder->status === 'waiting_approval'
                    && ! $purchaseOrder->hasNonDraftReceivingActivity();

            if (! $allowed) {
                throw ValidationException::withMessages([
                    'status' => 'Status PO sudah berubah atau penerimaannya tidak lagi draft. PO yang sudah diposting tidak dapat direvisi. Muat ulang daftar PO sebelum melanjutkan.',
                ]);
            }

            if ($status === 'approved') {
                $errors = [];

                if ($purchaseOrder->details->isEmpty()) {
                    $errors['obat_id'] = 'PO harus memiliki sedikitnya satu item obat sebelum disetujui.';
                }

                foreach ($purchaseOrder?->details ?? [] as $index => $detail) {
                    $conversion = $detail->satuanKonversi;

                    if (! $conversion || (int) $conversion->obat_id !== (int) $detail->obat_id
                        || (float) $conversion->konversi <= 0 || ! $conversion->satuan) {
                        $errors['satuan_id.'.$index] = 'Lengkapi satuan konversi untuk item obat ke-'.($index + 1).'. PO belum dapat diproses.';
                    }
                }

                if ($errors !== []) {
                    throw ValidationException::withMessages($errors);
                }
            }

            return $this->PembelianRepository->updateStatus($id, $status, $approvedBy, $branchIds);
        }, 3);
    }

    public function deletePembelian($id, ?array $branchIds = null)
    {
        return DB::transaction(function () use ($id, $branchIds) {
            $Pembelian = $this->PembelianRepository->findByIdPembelian($id, $branchIds, true);

            if (! $Pembelian) {
                return false;
            }

            if (! $Pembelian->canDelete()) {
                throw ValidationException::withMessages([
                    'status' => 'PO yang sudah disetujui atau memiliki riwayat penerimaan tidak dapat dihapus. Buka approval terlebih dahulu jika belum ada penerimaan.',
                ]);
            }

            return $Pembelian->delete();
        }, 3);
    }

    public function insertPembelianDetails(int $purchaseOrderId, array $details): void
    {
        $timestamp = now();
        $rows = array_map(fn (array $detail) => [
            ...$detail,
            'purchase_order_id' => $purchaseOrderId,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $details);

        foreach (array_chunk($rows, 100) as $chunk) {
            PembelianDetailModel::insert($chunk);
        }
    }

    public function deletePembelianDetail($poId)
    {
        return PembelianDetailModel::where('purchase_order_id', $poId)->delete();
    }

    public function syncPembelianDetails(PembelianModel $purchaseOrder, array $details): void
    {
        // Keep the IDs used by saved receipt batches for medicines that remain in the PO.
        $existing = $purchaseOrder->details->keyBy('obat_id');
        $retainedIds = [];

        foreach ($details as $attributes) {
            $detail = $existing->get($attributes['obat_id']);
            if ($detail) {
                $detail->update($attributes);
            } else {
                $detail = $purchaseOrder->details()->create($attributes);
            }
            $retainedIds[] = $detail->id;
        }

        // Removed medicines remain in draft receipt snapshots until the draft is reviewed.
        // The foreign key sets their purchase_order_detail_id to null.
        $purchaseOrder->details()->whereNotIn('id', $retainedIds)->delete();
        $purchaseOrder->penerimaanBarang()->where('status', 'draft')->update(['requires_po_review' => true]);
    }

    public function getKonversiSatuan($obatId)
    {
        $KonversiSatuan = $this->PembelianRepository->getKonversiSatuan($obatId);

        return $KonversiSatuan;
    }
}
