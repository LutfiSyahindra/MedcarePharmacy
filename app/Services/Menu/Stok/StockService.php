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
    public function recordReceipt(PenerimaanBarangModel $penerimaan, PenerimaanBarangDetailModel $detail): KartuStokModel
    {
        $detail->loadMissing(['purchaseOrderDetail.satuanKonversi.satuan', 'obat.satuan']);
        $penerimaan->loadMissing('purchaseOrder');
        $qtyStock = $this->receiptStockQuantity($detail);
        $basePrice = $this->receiptBasePrice($detail);

        return $this->recordMovement([
            'obat_id' => $detail->obat_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => $qtyStock,
            'harga_beli' => $basePrice,
            'jenis_mutasi' => 'masuk',
            'tanggal_mutasi' => $penerimaan->posted_at ?: now(),
            'reference_type' => PenerimaanBarangModel::class,
            'reference_id' => $penerimaan->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $penerimaan->nomor_penerimaan,
            'keterangan' => 'Penerimaan barang dari PO ' . ($penerimaan->purchaseOrder->no_po ?? '-') . ' - ' . $this->conversionNote($detail, $qtyStock),
            'created_by' => $penerimaan->posted_by ?: Auth::id(),
        ]);
    }

    public function reverseReceipt(PenerimaanBarangModel $penerimaan, PenerimaanBarangDetailModel $detail): KartuStokModel
    {
        $detail->loadMissing(['purchaseOrderDetail.satuanKonversi.satuan', 'obat.satuan']);
        $qtyStock = $this->receiptStockQuantity($detail);
        $basePrice = $this->receiptBasePrice($detail);

        return $this->recordMovement([
            'obat_id' => $detail->obat_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => $qtyStock,
            'harga_beli' => $basePrice,
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
        $obat = MasterObatModel::lockForUpdate()->findOrFail($payload['obat_id']);
        $batch = $this->resolveBatch($payload, $obat, $isInbound, $tanggalMutasi);
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

    private function resolveBatch(array $payload, MasterObatModel $obat, bool $isInbound, Carbon $tanggalMutasi): StokBatchModel
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

            return $batch;
        }

        $batchNumber = trim((string) ($payload['no_batch'] ?? ''));

        if ($batchNumber === '') {
            throw ValidationException::withMessages([
                'no_batch' => 'Nomor batch wajib diisi.',
            ]);
        }

        $expiredDate = $this->parseDate($payload['expired_date'] ?? null, 'expired_date');

        if (! $isInbound) {
            throw ValidationException::withMessages([
                'stok_batch_id' => 'Pilih batch stok untuk mutasi keluar.',
            ]);
        }

        $batch = StokBatchModel::where('obat_id', $obat->id)
            ->where('no_batch', $batchNumber)
            ->whereDate('expired_date', $expiredDate)
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
            'last_movement_at' => $tanggalMutasi,
            'created_by' => $payload['created_by'] ?? Auth::id(),
        ]);
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
