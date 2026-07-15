<?php

namespace App\Services\Menu\Stok;

use App\Models\MarginsModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\RiwayatHargaModel;
use App\Models\Menu\Stok\StokBatchModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function recordReceipt(
        PenerimaanBarangModel $penerimaan,
        PenerimaanBarangDetailModel $detail,
        ?float $hargaJual = null,
        ?string $alasanHarga = null
    ): KartuStokModel
    {
        $detail->loadMissing(['purchaseOrderDetail.satuanKonversi.satuan', 'obat.satuan']);
        $penerimaan->loadMissing('purchaseOrder');
        $qtyStock = $this->receiptStockQuantity($detail);
        $basePrice = $this->receiptBasePrice($detail);

        $movement = $this->recordMovement([
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $detail->stok_batch_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => $qtyStock,
            'harga_beli' => $basePrice,
            'harga_jual' => $hargaJual,
            'alasan_harga' => $alasanHarga ?: 'Posting penerimaan ' . $penerimaan->nomor_penerimaan . ' dari PO ' . ($penerimaan->purchaseOrder->no_po ?? '-'),
            'diskon' => $detail->diskon ?? 0,
            'ppn' => $detail->ppn ?? 0,
            'jenis_mutasi' => 'masuk',
            'tanggal_mutasi' => $penerimaan->posted_at ?: now(),
            'reference_type' => PenerimaanBarangModel::class,
            'reference_id' => $penerimaan->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $penerimaan->nomor_penerimaan,
            'keterangan' => 'Penerimaan barang dari PO ' . ($penerimaan->purchaseOrder->no_po ?? '-') . ' - ' . $this->conversionNote($detail, $qtyStock),
            'created_by' => $penerimaan->posted_by ?: Auth::id(),
        ]);

        if (! $detail->stok_batch_id && $movement->stok_batch_id) {
            $detail->forceFill(['stok_batch_id' => $movement->stok_batch_id])->save();
        }

        return $movement;
    }

    public function reverseReceipt(PenerimaanBarangModel $penerimaan, PenerimaanBarangDetailModel $detail): KartuStokModel
    {
        $detail->loadMissing(['purchaseOrderDetail.satuanKonversi.satuan', 'obat.satuan']);
        $qtyStock = $this->receiptStockQuantity($detail);
        $basePrice = $this->receiptBasePrice($detail);

        return $this->recordMovement([
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $detail->stok_batch_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => $qtyStock,
            'harga_beli' => $basePrice,
            'harga_jual' => null,
            'diskon' => $detail->diskon ?? 0,
            'ppn' => $detail->ppn ?? 0,
            'allow_identity_outbound' => true,
            'jenis_mutasi' => 'pembatalan_penerimaan',
            'tanggal_mutasi' => $penerimaan->cancelled_at ?: now(),
            'reference_type' => PenerimaanBarangModel::class,
            'reference_id' => $penerimaan->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $penerimaan->nomor_penerimaan,
            'keterangan' => 'Pembatalan penerimaan barang - ' . $this->conversionNote($detail, $qtyStock),
            'created_by' => $penerimaan->cancelled_by ?: Auth::id(),
        ]);
    }

    public function recordManualMutation(array $data): KartuStokModel
    {
        $jenisMutasi = $data['jenis_mutasi'];
        $isInbound = in_array($jenisMutasi, ['masuk', 'penyesuaian_masuk'], true);

        $payload = [
            'obat_id' => $data['obat_id'],
            'stok_batch_id' => $data['stok_batch_id'] ?? null,
            'no_batch' => $data['no_batch'] ?? null,
            'expired_date' => $data['expired_date'] ?? null,
            'qty' => $data['qty'],
            'harga_beli' => $data['harga_beli'] ?? 0,
            'harga_jual' => $data['harga_jual'] ?? null,
            'alasan_harga' => $data['alasan_harga'] ?? null,
            'diskon' => array_key_exists('diskon', $data) ? $data['diskon'] : null,
            'ppn' => array_key_exists('ppn', $data) ? $data['ppn'] : null,
            'jenis_mutasi' => $jenisMutasi,
            'tanggal_mutasi' => $data['tanggal_mutasi'] ?? now(),
            'nomor_referensi' => $data['nomor_referensi'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'created_by' => Auth::id(),
        ];

        if (! $isInbound && empty($payload['stok_batch_id'])) {
            throw ValidationException::withMessages([
                'stok_batch_id' => 'Pilih batch untuk mutasi stok keluar.',
            ]);
        }

        return $this->recordMovement($payload);
    }

    public function updateBatchSellingPrice(int $batchId, float $hargaJualBaru, string $alasan, ?int $changedBy = null): array
    {
        $batch = StokBatchModel::lockForUpdate()->findOrFail($batchId);

        return $this->applyBatchSellingPrice($batch, $hargaJualBaru, $alasan, $changedBy);
    }

    public function updateBatchSellingPriceFromMargin(int $batchId, string $alasan, ?int $changedBy = null): array
    {
        $batch = StokBatchModel::with(['obat.golongan'])->lockForUpdate()->findOrFail($batchId);
        $marginPrice = $this->batchSellingPriceMarginPreview($batch);

        return $this->applyBatchSellingPrice($batch, $marginPrice['harga_jual'], $alasan, $changedBy) + [
            'margin_price' => $marginPrice,
        ];
    }

    public function batchSellingPriceMarginPreview(StokBatchModel $batch): array
    {
        $batch->loadMissing(['obat.golongan']);

        return $this->calculateBatchSellingPriceFromMargin($batch);
    }

    private function applyBatchSellingPrice(StokBatchModel $batch, float $hargaJualBaru, string $alasan, ?int $changedBy = null): array
    {
        $hargaJualLama = (float) ($batch->harga_jual ?? 0);
        $hargaJualBaru = max(0, round($hargaJualBaru, 2));

        if ($this->samePrice($hargaJualLama, $hargaJualBaru)) {
            return [
                'batch' => $batch,
                'changed' => false,
                'harga_jual_lama' => $hargaJualLama,
                'harga_jual_baru' => $hargaJualBaru,
            ];
        }

        $batch->harga_jual = $hargaJualBaru;
        $batch->save();

        $this->recordPriceHistory($batch, $hargaJualLama, $hargaJualBaru, [
            'alasan_harga' => $alasan,
            'jenis_mutasi' => 'perubahan_harga',
            'created_by' => $changedBy ?? Auth::id(),
        ]);

        return [
            'batch' => $batch,
            'changed' => true,
            'harga_jual_lama' => $hargaJualLama,
            'harga_jual_baru' => $hargaJualBaru,
        ];
    }

    private function recordMovement(array $payload): KartuStokModel
    {
        $qty = (float) ($payload['qty'] ?? 0);

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Qty mutasi harus lebih dari 0.',
            ]);
        }

        $jenisMutasi = $payload['jenis_mutasi'];
        $isInbound = in_array($jenisMutasi, ['masuk', 'penyesuaian_masuk'], true);
        $direction = $isInbound ? 1 : -1;
        $tanggalMutasi = $this->parseDateTime($payload['tanggal_mutasi'] ?? now());
        $hasDiscountPayload = array_key_exists('diskon', $payload) && $payload['diskon'] !== null && $payload['diskon'] !== '';
        $hasTaxPayload = array_key_exists('ppn', $payload) && $payload['ppn'] !== null && $payload['ppn'] !== '';
        $diskon = $hasDiscountPayload ? $this->discountPercent($payload['diskon']) : 0;
        $ppn = $hasTaxPayload ? $this->percent($payload['ppn']) : 0;
        $obat = MasterObatModel::lockForUpdate()->findOrFail($payload['obat_id']);
        $batch = $this->resolveBatch($payload, $obat, $isInbound, $tanggalMutasi, $diskon, $ppn, $hasDiscountPayload, $hasTaxPayload);
        $diskon = $hasDiscountPayload ? $diskon : (float) ($batch->diskon ?? 0);
        $ppn = $hasTaxPayload ? $ppn : (float) ($batch->ppn ?? 0);
        $hargaJualLama = (float) ($batch->harga_jual ?? 0);
        $hargaJualBaru = null;
        $nextBatchQty = (float) $batch->qty + ($direction * $qty);

        if ($nextBatchQty < -0.00001) {
            throw ValidationException::withMessages([
                'qty' => 'Qty keluar melebihi stok batch tersedia.',
            ]);
        }

        $batch->qty = max(0, $nextBatchQty);
        $batch->last_movement_at = $tanggalMutasi;

        if ($isInbound) {
            $batch->harga_beli = (float) ($payload['harga_beli'] ?: $batch->harga_beli);
            $batch->diskon = $diskon;
            $batch->ppn = $ppn;
            $hargaJual = $this->optionalPrice($payload['harga_jual'] ?? null);

            if ($hargaJual !== null) {
                $hargaJualBaru = $hargaJual;
                $batch->harga_jual = $hargaJual;
            }
        }

        $batch->save();

        if ($isInbound && $hargaJualBaru !== null && ! $this->samePrice($hargaJualLama, $hargaJualBaru)) {
            $this->recordPriceHistory($batch, $hargaJualLama, $hargaJualBaru, $payload);
        }

        $saldoTotal = (float) StokBatchModel::where('obat_id', $obat->id)->sum('qty');

        return KartuStokModel::create([
            'obat_id' => $obat->id,
            'stok_batch_id' => $batch->id,
            'tanggal_mutasi' => $tanggalMutasi,
            'jenis_mutasi' => $jenisMutasi,
            'qty_masuk' => $isInbound ? $qty : 0,
            'qty_keluar' => $isInbound ? 0 : $qty,
            'saldo_batch' => $batch->qty,
            'saldo_total' => $saldoTotal,
            'no_batch' => $batch->no_batch,
            'expired_date' => $batch->expired_date,
            'harga_beli' => $batch->harga_beli,
            'reference_type' => $payload['reference_type'] ?? null,
            'reference_id' => $payload['reference_id'] ?? null,
            'reference_detail_id' => $payload['reference_detail_id'] ?? null,
            'nomor_referensi' => $payload['nomor_referensi'] ?? null,
            'keterangan' => $payload['keterangan'] ?? null,
            'created_by' => $payload['created_by'] ?? Auth::id(),
        ]);
    }

    private function resolveBatch(
        array $payload,
        MasterObatModel $obat,
        bool $isInbound,
        Carbon $tanggalMutasi,
        float $diskon,
        float $ppn,
        bool $hasDiscountPayload = true,
        bool $hasTaxPayload = true
    ): StokBatchModel
    {
        if (! empty($payload['stok_batch_id'])) {
            $batch = StokBatchModel::where('obat_id', $obat->id)
                ->where('id', $payload['stok_batch_id'])
                ->lockForUpdate()
                ->first();

            if (! $batch) {
                throw ValidationException::withMessages([
                    'stok_batch_id' => 'Batch stok tidak ditemukan.',
                ]);
            }

            if ($isInbound && ! $hasDiscountPayload && ! $hasTaxPayload) {
                return $batch;
            }

            $discountMatches = ! $hasDiscountPayload || $this->samePercent((float) ($batch->diskon ?? 0), $diskon);
            $taxMatches = ! $hasTaxPayload || $this->samePercent((float) ($batch->ppn ?? 0), $ppn);

            if ($isInbound && (! $discountMatches || ! $taxMatches)) {
                return $this->resolveInboundBatchByIdentity(
                    $payload,
                    $obat,
                    $tanggalMutasi,
                    $hasDiscountPayload ? $diskon : (float) ($batch->diskon ?? 0),
                    $hasTaxPayload ? $ppn : (float) ($batch->ppn ?? 0)
                );
            }

            return $batch;
        }

        $batchNumber = trim((string) ($payload['no_batch'] ?? ''));

        if ($batchNumber === '') {
            throw ValidationException::withMessages([
                'no_batch' => 'Nomor batch wajib diisi.',
            ]);
        }

        if (! $isInbound) {
            if (! empty($payload['allow_identity_outbound'])) {
                return $this->resolveOutboundBatchByIdentity($payload, $obat, $diskon, $ppn);
            }

            throw ValidationException::withMessages([
                'stok_batch_id' => 'Pilih batch stok untuk mutasi keluar.',
            ]);
        }

        return $this->resolveInboundBatchByIdentity($payload, $obat, $tanggalMutasi, $diskon, $ppn);
    }

    private function resolveInboundBatchByIdentity(array $payload, MasterObatModel $obat, Carbon $tanggalMutasi, float $diskon, float $ppn): StokBatchModel
    {
        $batchNumber = trim((string) ($payload['no_batch'] ?? ''));

        if ($batchNumber === '') {
            throw ValidationException::withMessages([
                'no_batch' => 'Nomor batch wajib diisi.',
            ]);
        }

        $expiredDate = $this->parseOptionalDate($payload['expired_date'] ?? null, 'expired_date');
        $batch = StokBatchModel::where('obat_id', $obat->id)
            ->where('no_batch', $batchNumber)
            ->when(
                $expiredDate === null,
                fn ($query) => $query->whereNull('expired_date'),
                fn ($query) => $query->whereDate('expired_date', $expiredDate)
            )
            ->where('diskon', $diskon)
            ->where('ppn', $ppn)
            ->lockForUpdate()
            ->first();

        if ($batch) {
            return $batch;
        }

        return StokBatchModel::create([
            'obat_id' => $obat->id,
            'no_batch' => $batchNumber,
            'expired_date' => $expiredDate,
            'qty' => 0,
            'harga_beli' => (float) ($payload['harga_beli'] ?? 0),
            'harga_jual' => 0,
            'diskon' => $diskon,
            'ppn' => $ppn,
            'last_movement_at' => $tanggalMutasi,
            'created_by' => $payload['created_by'] ?? Auth::id(),
        ]);
    }

    private function recordPriceHistory(StokBatchModel $batch, float $hargaJualLama, float $hargaJualBaru, array $payload): void
    {
        RiwayatHargaModel::create([
            'obat_id' => $batch->obat_id,
            'stok_batch_id' => $batch->id,
            'harga_jual_lama' => $hargaJualLama,
            'harga_jual_baru' => $hargaJualBaru,
            'alasan' => $this->priceHistoryReason($payload),
            'changed_by' => $payload['created_by'] ?? Auth::id(),
            'created_at' => now(),
        ]);
    }

    private function calculateBatchSellingPriceFromMargin(StokBatchModel $batch): array
    {
        $obat = $batch->obat;

        if (! $obat) {
            throw ValidationException::withMessages([
                'stok_batch_id' => 'Obat pada batch stok tidak ditemukan.',
            ]);
        }

        $margin = $this->activeGolonganMargin($obat);
        $faktorJual = $margin ? (float) $margin->faktor_jual : 1.0;
        $diskon = $this->discountPercent($batch->diskon ?? 0);
        $ppn = $this->percent($batch->ppn ?? 0);
        $hargaBeli = max(0, (float) ($batch->harga_beli ?? 0));
        $hargaBeliDasar = max(0, $hargaBeli - ($hargaBeli * ($diskon / 100)));
        $hargaBeliIncludePpn = $hargaBeliDasar * (1 + ($ppn / 100));

        return [
            'harga_jual' => round($hargaBeliIncludePpn * $faktorJual, 2),
            'harga_beli_dasar' => round($hargaBeliDasar, 2),
            'harga_beli_include_ppn' => round($hargaBeliIncludePpn, 2),
            'faktor_jual' => round($faktorJual, 3),
            'ppn' => $ppn,
            'has_margin' => (bool) $margin,
            'margin_tingkat' => $margin?->tingkat,
            'margin_reference' => $margin ? ($obat->golongan->nama ?? null) : null,
        ];
    }

    private function activeGolonganMargin(MasterObatModel $obat): ?MarginsModel
    {
        if (! $obat->golongan_id) {
            return null;
        }

        return MarginsModel::where('tingkat', 'golongan')
            ->where('reference_id', $obat->golongan_id)
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    private function priceHistoryReason(array $payload): string
    {
        $reason = trim((string) ($payload['alasan_harga'] ?? ''));

        if ($reason !== '') {
            return $reason;
        }

        $label = match ($payload['jenis_mutasi'] ?? '') {
            'masuk' => 'Mutasi stok masuk',
            'penyesuaian_masuk' => 'Penyesuaian stok masuk',
            default => 'Perubahan harga jual batch',
        };
        $reference = trim((string) ($payload['nomor_referensi'] ?? ''));
        $note = trim((string) ($payload['keterangan'] ?? ''));

        return collect([$label, $reference, $note])
            ->filter()
            ->implode(' - ');
    }

    private function resolveOutboundBatchByIdentity(array $payload, MasterObatModel $obat, float $diskon, float $ppn): StokBatchModel
    {
        $batchNumber = trim((string) ($payload['no_batch'] ?? ''));

        if ($batchNumber === '') {
            throw ValidationException::withMessages([
                'no_batch' => 'Nomor batch wajib diisi.',
            ]);
        }

        $expiredDate = $this->parseOptionalDate($payload['expired_date'] ?? null, 'expired_date');
        $batch = StokBatchModel::where('obat_id', $obat->id)
            ->where('no_batch', $batchNumber)
            ->when(
                $expiredDate === null,
                fn ($query) => $query->whereNull('expired_date'),
                fn ($query) => $query->whereDate('expired_date', $expiredDate)
            )
            ->where('diskon', $diskon)
            ->where('ppn', $ppn)
            ->lockForUpdate()
            ->first();

        if (! $batch) {
            throw ValidationException::withMessages([
                'stok_batch_id' => 'Batch stok penerimaan tidak ditemukan.',
            ]);
        }

        return $batch;
    }

    private function optionalPrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, (float) $value);
    }

    private function discountPercent($value): float
    {
        return $this->percent($value);
    }

    private function sameDiscount(float $left, float $right): bool
    {
        return $this->samePercent($left, $right);
    }

    private function percent($value): float
    {
        return round(min(100, max(0, (float) ($value ?: 0))), 2);
    }

    private function samePercent(float $left, float $right): bool
    {
        return abs($this->percent($left) - $this->percent($right)) < 0.00001;
    }

    private function samePrice(float $left, float $right): bool
    {
        return abs(round($left, 2) - round($right, 2)) < 0.01;
    }

    private function receiptStockQuantity(PenerimaanBarangDetailModel $detail): float
    {
        $storedQty = (float) ($detail->qty_diterima_stok ?? 0);

        if ($storedQty > 0) {
            return $storedQty;
        }

        return (float) $detail->qty_diterima * $this->receiptConversion($detail);
    }

    private function receiptBasePrice(PenerimaanBarangDetailModel $detail): float
    {
        $storedPrice = (float) ($detail->harga_beli_stok ?? 0);

        if ($storedPrice > 0) {
            return $storedPrice;
        }

        $conversion = $this->receiptConversion($detail);

        return $conversion > 0 ? (float) $detail->harga_beli / $conversion : (float) $detail->harga_beli;
    }

    private function receiptConversion(PenerimaanBarangDetailModel $detail): float
    {
        $storedConversion = (float) ($detail->konversi_satuan ?? 0);

        if ($storedConversion > 0) {
            return $storedConversion;
        }

        return max(1, (float) ($detail->purchaseOrderDetail?->satuanKonversi?->konversi ?? 1));
    }

    private function conversionNote(PenerimaanBarangDetailModel $detail, float $qtyStock): string
    {
        $conversion = $this->receiptConversion($detail);
        $purchaseUnit = $detail->satuan_beli
            ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama ?? 'satuan');
        $stockUnit = $detail->satuan_stok
            ?: ($detail->obat?->satuan?->nama ?? 'satuan stok');

        return number_format((float) $detail->qty_diterima, 2, ',', '.') . ' ' . $purchaseUnit
            . ' x ' . number_format($conversion, 2, ',', '.')
            . ' = ' . number_format($qtyStock, 2, ',', '.') . ' ' . $stockUnit;
    }

    private function parseDate($date, string $field): string
    {
        $value = trim((string) $date);

        if ($value === '') {
            throw ValidationException::withMessages([
                $field => 'Expired date wajib diisi.',
            ]);
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Format tanggal tidak valid.',
            ]);
        }
    }

    private function parseOptionalDate($date, string $field): ?string
    {
        $value = trim((string) $date);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Format tanggal tidak valid.',
            ]);
        }
    }

    private function parseDateTime($date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return now();
        }
    }
}
