<?php

namespace App\Services\Menu\Stok;

use App\Models\MasterObatModel;
use App\Models\Menu\Stok\StockOpnameDetailModel;
use App\Models\Menu\Stok\StockOpnameLogModel;
use App\Models\Menu\Stok\StockOpnameModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Services\Settings\Auth\RoleSettingService;
use App\Support\BranchAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOpnameService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly RoleSettingService $roleSettings
    ) {}

    public function start(int $id): StockOpnameModel
    {
        return DB::transaction(function () use ($id) {
            $opname = $this->findAccessibleForUpdate($id);
            $this->requireOpnameAuthorization($opname);
            $this->requireStatus($opname, [StockOpnameModel::STATUS_DRAFT], 'Hanya draft yang dapat mulai dihitung.');

            $overlap = StockOpnameModel::query()
                ->where('branch_id', $opname->branch_id)
                ->whereKeyNot($opname->id)
                ->whereIn('status', StockOpnameModel::activeStatuses())
                ->where(function ($query) use ($opname) {
                    $query->whereNull('rak_id');

                    if ($opname->rak_id === null) {
                        $query->orWhereNotNull('rak_id');
                    } else {
                        $query->orWhere('rak_id', $opname->rak_id);
                    }
                })
                ->lockForUpdate()
                ->first();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'rak_id' => 'Area stok masih tercakup dalam opname aktif '.$overlap->nomor.'.',
                ]);
            }

            $medicines = MasterObatModel::query()
                ->with(['satuan', 'rakPenyimpanan'])
                ->when($opname->rak_id, fn ($query) => $query->where('rak_id', $opname->rak_id))
                ->orderBy('nama_obat')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($medicines->isEmpty()) {
                throw ValidationException::withMessages([
                    'rak_id' => 'Tidak ada obat pada lokasi yang dipilih.',
                ]);
            }

            $batches = StokBatchModel::query()
                ->where('branch_id', $opname->branch_id)
                ->whereIn('obat_id', $medicines->pluck('id'))
                ->orderBy('obat_id')
                ->orderBy('expired_date')
                ->orderBy('no_batch')
                ->lockForUpdate()
                ->get()
                ->groupBy('obat_id');

            $totalRows = 0;
            $medicinesWithoutBatch = 0;

            foreach ($medicines as $medicine) {
                $medicineBatches = $batches->get($medicine->id, collect());

                if ($medicineBatches->isEmpty()) {
                    $medicineBatches = collect([null]);
                    $medicinesWithoutBatch++;
                }

                foreach ($medicineBatches as $batch) {
                    StockOpnameDetailModel::create([
                        'stock_opname_id' => $opname->id,
                        'stok_batch_id' => $batch?->id,
                        'obat_id' => $medicine->id,
                        'rak_id' => $medicine->rak_id,
                        'kode_obat' => $medicine->kode_obat,
                        'nama_obat' => $medicine->nama_obat ?: 'Obat #'.$medicine->id,
                        'satuan' => $medicine->satuan?->nama,
                        'no_batch' => $batch?->no_batch ?: 'Belum ada stok/batch',
                        'expired_date' => $batch?->expired_date,
                        'hpp' => $batch?->harga_beli ?? $medicine->harga_beli ?? 0,
                        'stok_sistem_awal' => $batch?->qty ?? 0,
                    ]);
                    $totalRows++;
                }
            }

            $from = $opname->status;
            $opname->forceFill([
                'status' => StockOpnameModel::STATUS_COUNTING,
                'transaction_mode' => StockOpnameModel::MODE_FREEZE,
                'started_by' => Auth::id(),
                'started_at' => now(),
            ])->save();

            $this->log($opname, 'start_counting', $from, $opname->status, 'Snapshot seluruh obat dan stok awal dibuat.', [
                'total_obat' => $medicines->count(),
                'total_baris' => $totalRows,
                'obat_tanpa_batch' => $medicinesWithoutBatch,
                'transaction_mode' => $opname->transaction_mode,
            ]);

            return $opname->fresh();
        });
    }

    public function saveCounts(int $id, array $rows): StockOpnameModel
    {
        return DB::transaction(function () use ($id, $rows) {
            $opname = $this->findAccessibleForUpdate($id);
            $this->requireBranchOfficerAssignment($opname);
            $this->requireStatus($opname, [StockOpnameModel::STATUS_COUNTING], 'Penghitungan hanya dapat diisi pada status Proses Penghitungan.');

            foreach ($rows as $row) {
                $detail = $opname->details()->whereKey($row['id'])->lockForUpdate()->firstOrFail();
                $physical = round((float) $row['stok_fisik'], 2);

                $detail->forceFill([
                    'stok_fisik' => $physical,
                    'stok_sistem_hitung' => null,
                    'selisih' => null,
                    'alasan_selisih' => null,
                    'counted_by' => Auth::id(),
                    'counted_at' => now(),
                ])->save();
            }

            $this->log($opname, 'save_counts', $opname->status, $opname->status, 'Hasil blind count disimpan.', [
                'saved_rows' => count($rows),
            ]);

            return $opname->fresh();
        });
    }

    public function submit(int $id): StockOpnameModel
    {
        return DB::transaction(function () use ($id) {
            $opname = $this->findAccessibleForUpdate($id);
            $this->requireBranchOfficerAssignment($opname);
            $this->requireStatus($opname, [StockOpnameModel::STATUS_COUNTING], 'Hanya hasil penghitungan yang dapat dikirim untuk verifikasi.');
            $details = $opname->details()->lockForUpdate()->get();

            if ($details->isEmpty() || $details->contains(fn ($detail) => $detail->stok_fisik === null || $detail->counted_at === null)) {
                throw ValidationException::withMessages([
                    'details' => 'Semua obat dan batch wajib dihitung sebelum dikirim untuk verifikasi.',
                ]);
            }

            foreach ($details as $detail) {
                $batch = $detail->stok_batch_id
                    ? StokBatchModel::query()
                        ->where('branch_id', $opname->branch_id)
                        ->whereKey($detail->stok_batch_id)
                        ->lockForUpdate()
                        ->first()
                    : null;
                $systemStock = round((float) ($batch?->qty ?? 0), 2);
                $physicalStock = round((float) $detail->stok_fisik, 2);

                $detail->forceFill([
                    'stok_sistem_hitung' => $systemStock,
                    'selisih' => round($physicalStock - $systemStock, 2),
                    'hpp' => (float) ($batch?->harga_beli ?? $detail->hpp ?? 0),
                    'alasan_selisih' => null,
                ])->save();
            }

            $from = $opname->status;
            $opname->forceFill([
                'status' => StockOpnameModel::STATUS_AWAITING_VERIFICATION,
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
                'verification_note' => null,
            ])->save();

            $this->log($opname, 'submit_verification', $from, $opname->status, 'Hasil fisik disubmit oleh petugas. Stok sistem dibuka untuk review dan operasional stok/POS kembali aktif.');

            return $opname->fresh();
        });
    }

    public function saveReasons(int $id, array $rows): StockOpnameModel
    {
        return DB::transaction(function () use ($id, $rows) {
            $opname = $this->findAccessibleForUpdate($id);
            $this->requireBranchOfficerAssignment($opname);
            $this->requireStatus($opname, [StockOpnameModel::STATUS_AWAITING_VERIFICATION], 'Alasan selisih hanya dapat diisi pada tahap Review Selisih.');
            $submittedRows = collect($rows)->keyBy(fn ($row) => (int) $row['id']);
            $details = $opname->details()->lockForUpdate()->get();

            foreach ($details as $detail) {
                $row = $submittedRows->get((int) $detail->id);
                $reason = trim((string) ($row['alasan_selisih'] ?? ''));

                if (abs((float) $detail->selisih) >= 0.005 && $reason === '') {
                    throw ValidationException::withMessages([
                        'details' => 'Alasan wajib diisi untuk '.$detail->nama_obat.' batch '.$detail->no_batch.'.',
                    ]);
                }

                $detail->forceFill([
                    'alasan_selisih' => abs((float) $detail->selisih) >= 0.005 ? $reason : null,
                ])->save();
            }

            $this->log($opname, 'save_reasons', $opname->status, $opname->status, 'Alasan seluruh item selisih disimpan.', [
                'difference_rows' => $details->filter(fn ($detail) => abs((float) $detail->selisih) >= 0.005)->count(),
            ]);

            return $opname->fresh();
        });
    }

    public function verify(int $id, ?string $note = null): StockOpnameModel
    {
        return DB::transaction(function () use ($id, $note) {
            $opname = $this->findAccessibleForUpdate($id);
            $this->requireBranchOfficerAssignment($opname);
            $this->requireStatus($opname, [StockOpnameModel::STATUS_AWAITING_VERIFICATION], 'Dokumen belum menunggu verifikasi.');
            $details = $opname->details()->lockForUpdate()->get();

            $missingReason = $details->first(fn ($detail) => abs((float) $detail->selisih) >= 0.005 && trim((string) $detail->alasan_selisih) === '');

            if ($missingReason) {
                throw ValidationException::withMessages([
                    'alasan_selisih' => 'Alasan selisih '.$missingReason->nama_obat.' batch '.$missingReason->no_batch.' wajib diisi sebelum validasi.',
                ]);
            }

            $this->reconcile($opname);
            $from = $opname->status;
            $opname->forceFill([
                'status' => StockOpnameModel::STATUS_AWAITING_APPROVAL,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'verification_note' => $this->nullableText($note),
            ])->save();

            $this->log($opname, 'verify', $from, $opname->status, $note ?: 'Hasil hitung divalidasi oleh petugas dan stok target telah disesuaikan dengan transaksi berjalan.', $this->summary($opname));

            return $opname->fresh();
        });
    }

    public function approve(int $id, ?string $note = null): StockOpnameModel
    {
        return DB::transaction(function () use ($id, $note) {
            $opname = $this->findAccessibleForUpdate($id);
            $this->requireOpnameAuthorization($opname);
            $this->requireStatus($opname, [StockOpnameModel::STATUS_AWAITING_APPROVAL], 'Dokumen belum menunggu persetujuan.');

            $from = $opname->status;
            $opname->forceFill([
                'status' => StockOpnameModel::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_note' => $this->nullableText($note),
            ])->save();

            $this->log($opname, 'approve', $from, $opname->status, $note ?: 'Stock opname disetujui.');

            return $opname->fresh();
        });
    }

    public function reject(int $id, string $note): StockOpnameModel
    {
        return DB::transaction(function () use ($id, $note) {
            $opname = $this->findAccessibleForUpdate($id);
            $this->requireStatus($opname, [
                StockOpnameModel::STATUS_AWAITING_VERIFICATION,
                StockOpnameModel::STATUS_AWAITING_APPROVAL,
            ], 'Dokumen pada status ini tidak dapat dikembalikan.');

            if ($opname->status === StockOpnameModel::STATUS_AWAITING_VERIFICATION) {
                $this->requireValidationAuthorization($opname);
            } else {
                $this->requireOpnameAuthorization($opname);
            }

            $from = $opname->status;
            $opname->forceFill([
                'status' => StockOpnameModel::STATUS_COUNTING,
                'verification_note' => $from === StockOpnameModel::STATUS_AWAITING_VERIFICATION ? $note : $opname->verification_note,
                'approval_note' => $from === StockOpnameModel::STATUS_AWAITING_APPROVAL ? $note : null,
                'submitted_by' => null,
                'submitted_at' => null,
                'verified_by' => null,
                'verified_at' => null,
                'approved_by' => null,
                'approved_at' => null,
            ])->save();

            foreach ($opname->details()->with('batch')->lockForUpdate()->get() as $detail) {
                $detail->forceFill([
                    'stok_sistem_awal' => (float) ($detail->batch?->qty ?? 0),
                    'hpp' => (float) ($detail->batch?->harga_beli ?? $detail->hpp ?? 0),
                    'stok_sistem_hitung' => null,
                    'stok_fisik' => null,
                    'selisih' => null,
                    'alasan_selisih' => null,
                    'mutasi_masuk' => 0,
                    'mutasi_keluar' => 0,
                    'stok_sistem_validasi' => null,
                    'stok_target_validasi' => null,
                    'selisih_validasi' => null,
                    'counted_by' => null,
                    'counted_at' => null,
                ])->save();
            }

            $this->log($opname, 'reject', $from, $opname->status, $note);

            return $opname->fresh();
        });
    }

    public function adjust(int $id): StockOpnameModel
    {
        return DB::transaction(function () use ($id) {
            $opname = $this->findAccessibleForUpdate($id);
            $this->requireOpnameAuthorization($opname);
            $this->requireStatus($opname, [StockOpnameModel::STATUS_APPROVED], 'Penyesuaian hanya dapat diposting setelah stock opname disetujui.');

            $this->reconcile($opname);
            $details = $opname->details()->with('batch')->lockForUpdate()->get();
            $adjustedRows = 0;
            $summary = $this->summary($opname);

            foreach ($details as $detail) {
                if (abs((float) $detail->selisih_validasi) < 0.005) {
                    continue;
                }

                $movement = $this->stockService->recordStockOpnameAdjustment($opname, $detail, Auth::id());
                $attributes = ['kartu_stok_id' => $movement->id];

                if ($detail->stok_batch_id === null) {
                    $attributes['stok_batch_id'] = $movement->stok_batch_id;
                    $attributes['no_batch'] = $movement->no_batch;
                    $attributes['expired_date'] = $movement->expired_date;
                }

                $detail->forceFill($attributes)->save();
                $adjustedRows++;
            }

            $from = $opname->status;
            $opname->forceFill([
                'status' => StockOpnameModel::STATUS_ADJUSTED,
                'adjusted_by' => Auth::id(),
                'adjusted_at' => now(),
                'posting_summary' => $summary + ['adjusted_rows' => $adjustedRows],
            ])->save();

            $this->log($opname, 'post_adjustment', $from, $opname->status, 'Penyesuaian stok diposting ke kartu stok.', [
                'adjusted_rows' => $adjustedRows,
            ]);

            return $opname->fresh();
        });
    }

    public function summary(StockOpnameModel $opname): array
    {
        $details = $opname->relationLoaded('details') ? $opname->details : $opname->details()->get();
        $difference = fn ($detail) => (float) ($detail->selisih_validasi ?? $detail->selisih ?? 0);
        $minusRows = $details->filter(fn ($detail) => $difference($detail) < -0.005);
        $plusRows = $details->filter(fn ($detail) => $difference($detail) > 0.005);

        return [
            'total_items' => $details->count(),
            'matching_items' => $details->filter(fn ($detail) => abs($difference($detail)) < 0.005)->count(),
            'minus_items' => $minusRows->count(),
            'plus_items' => $plusRows->count(),
            'system_qty' => round((float) $details->sum('stok_sistem_hitung'), 2),
            'physical_qty' => round((float) $details->sum('stok_fisik'), 2),
            'movement_in_qty' => round((float) $details->sum('mutasi_masuk'), 2),
            'movement_out_qty' => round((float) $details->sum('mutasi_keluar'), 2),
            'target_qty' => round((float) $details->sum(fn ($detail) => (float) ($detail->stok_target_validasi ?? $detail->stok_fisik ?? 0)), 2),
            'current_system_qty' => round((float) $details->sum(fn ($detail) => (float) ($detail->stok_sistem_validasi ?? $detail->stok_sistem_hitung ?? 0)), 2),
            'minus_qty' => round(abs((float) $minusRows->sum(fn ($detail) => $difference($detail))), 2),
            'plus_qty' => round((float) $plusRows->sum(fn ($detail) => $difference($detail)), 2),
            'loss_value' => round(abs((float) $minusRows->sum(fn ($detail) => $difference($detail) * (float) $detail->hpp)), 2),
            'surplus_value' => round((float) $plusRows->sum(fn ($detail) => $difference($detail) * (float) $detail->hpp), 2),
            'net_value' => round((float) $details->sum(fn ($detail) => $difference($detail) * (float) $detail->hpp), 2),
        ];
    }

    private function reconcile(StockOpnameModel $opname): void
    {
        $details = $opname->details()->with('batch')->lockForUpdate()->get();

        foreach ($details as $detail) {
            $movementQuery = $opname->movements()
                ->where('stock_opname_detail_id', $detail->id);

            if ($opname->submitted_at) {
                $movementQuery->where('created_at', '>=', $opname->submitted_at);
            }

            $movementIn = round((float) (clone $movementQuery)->sum('qty_masuk'), 2);
            $movementOut = round((float) (clone $movementQuery)->sum('qty_keluar'), 2);
            $currentSystem = round((float) ($detail->batch?->qty ?? 0), 2);
            $target = round((float) $detail->stok_fisik + $movementIn - $movementOut, 2);

            $detail->forceFill([
                'mutasi_masuk' => $movementIn,
                'mutasi_keluar' => $movementOut,
                'stok_sistem_validasi' => $currentSystem,
                'stok_target_validasi' => $target,
                'selisih_validasi' => round($target - $currentSystem, 2),
            ])->save();
        }

        $opname->setRelation('details', $details->map(fn ($detail) => $detail->fresh()));
    }

    private function findAccessibleForUpdate(int $id): StockOpnameModel
    {
        return StockOpnameModel::query()
            ->whereIn('branch_id', BranchAccess::userBranchIds())
            ->lockForUpdate()
            ->findOrFail($id);
    }

    private function requireStatus(StockOpnameModel $opname, array $statuses, string $message): void
    {
        if (! in_array($opname->status, $statuses, true)) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }

    private function requireOpnameAuthorization(StockOpnameModel $opname): void
    {
        abort_unless(
            $this->roleSettings->canApproveBranch(Auth::user(), (int) $opname->branch_id),
            403,
            'Hanya admin atau apoteker yang berwenang mengatur status stock opname.'
        );
    }

    private function requireValidationAuthorization(StockOpnameModel $opname): void
    {
        abort_unless(
            $this->roleSettings->canValidateStockOpnameBranch(Auth::user(), (int) $opname->branch_id),
            403,
            'Role Anda tidak dikonfigurasi sebagai validator stock opname untuk cabang ini.'
        );
    }

    private function requireBranchOfficerAssignment(StockOpnameModel $opname): void
    {
        abort_unless(
            in_array((int) $opname->branch_id, BranchAccess::assignedUserBranchIds(), true),
            403,
            'Aksi stock opname ini hanya dapat dilakukan oleh user/petugas yang ditugaskan pada cabang terkait.'
        );
    }

    private function log(StockOpnameModel $opname, string $action, ?string $from, ?string $to, ?string $note = null, ?array $metadata = null): void
    {
        StockOpnameLogModel::create([
            'stock_opname_id' => $opname->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $this->nullableText($note),
            'metadata' => $metadata,
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);
    }

    private function nullableText(?string $text): ?string
    {
        $text = trim((string) $text);

        return $text !== '' ? $text : null;
    }
}
