<?php

namespace App\Services\Menu\Stok;

use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\StokBatchModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function recordReceipt(PenerimaanBarangModel $penerimaan, PenerimaanBarangDetailModel $detail, ?float $hargaJual = null): KartuStokModel
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
            'diskon' => $detail->diskon ?? 0,
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
            'diskon' => $data['diskon'] ?? 0,
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
        $diskon = $this->discountPercent($payload['diskon'] ?? 0);
        $obat = MasterObatModel::lockForUpdate()->findOrFail($payload['obat_id']);
        $batch = $this->resolveBatch($payload, $obat, $isInbound, $tanggalMutasi, $diskon);
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
            $hargaJual = $this->optionalPrice($payload['harga_jual'] ?? null);

            if ($hargaJual !== null) {
                $batch->harga_jual = $hargaJual;
            }
        }

        $batch->save();

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

    private function resolveBatch(array $payload, MasterObatModel $obat, bool $isInbound, Carbon $tanggalMutasi, float $diskon): StokBatchModel
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

            if ($isInbound && ! $this->sameDiscount((float) ($batch->diskon ?? 0), $diskon)) {
                return $this->resolveInboundBatchByIdentity($payload, $obat, $tanggalMutasi, $diskon);
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
                return $this->resolveOutboundBatchByIdentity($payload, $obat, $diskon);
            }

            throw ValidationException::withMessages([
                'stok_batch_id' => 'Pilih batch stok untuk mutasi keluar.',
            ]);
        }

        return $this->resolveInboundBatchByIdentity($payload, $obat, $tanggalMutasi, $diskon);
    }

    private function resolveInboundBatchByIdentity(array $payload, MasterObatModel $obat, Carbon $tanggalMutasi, float $diskon): StokBatchModel
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
            'harga_jual' => $this->optionalPrice($payload['harga_jual'] ?? null) ?? 0,
            'diskon' => $diskon,
            'last_movement_at' => $tanggalMutasi,
            'created_by' => $payload['created_by'] ?? Auth::id(),
        ]);
    }

    private function resolveOutboundBatchByIdentity(array $payload, MasterObatModel $obat, float $diskon): StokBatchModel
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
        return round(min(100, max(0, (float) ($value ?: 0))), 2);
    }

    private function sameDiscount(float $left, float $right): bool
    {
        return abs($this->discountPercent($left) - $this->discountPercent($right)) < 0.00001;
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
