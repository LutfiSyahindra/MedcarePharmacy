<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MIGRATION_NOTE = 'Migrasi stok dari penerimaan posted';
    private const RECEIPT_REFERENCE = 'App\\Models\\Menu\\PembelianPenerimaan\\PenerimaanBarangModel';

    public function up(): void
    {
        $details = DB::table('penerimaan_barang_detail as detail')
            ->join('penerimaan_barang as header', 'header.id', '=', 'detail.penerimaan_barang_id')
            ->where('header.status', 'posted')
            ->select([
                'detail.id',
                'detail.penerimaan_barang_id',
                'detail.obat_id',
                'detail.qty_diterima',
                'detail.qty_diterima_stok',
                'detail.konversi_satuan',
                'detail.satuan_beli',
                'detail.satuan_stok',
                'detail.no_batch',
                'detail.expired_date',
                'detail.harga_beli',
                'detail.harga_beli_stok',
                'header.nomor_penerimaan',
                'header.posted_at',
                'header.created_at',
                'header.posted_by',
            ])
            ->orderBy('header.posted_at')
            ->orderBy('detail.id')
            ->get();

        foreach ($details as $detail) {
            $conversion = max(1, (float) ($detail->konversi_satuan ?: 1));
            $qty = (float) ($detail->qty_diterima_stok ?: ((float) $detail->qty_diterima * $conversion));
            $basePrice = (float) ($detail->harga_beli_stok ?: ((float) $detail->harga_beli / $conversion));

            if ($qty <= 0) {
                continue;
            }

            $movementAt = Carbon::parse($detail->posted_at ?: $detail->created_at ?: now());
            $batchNumber = $detail->no_batch ?: ('RECEIPT-' . $detail->id);
            $expiredDate = $detail->expired_date ? Carbon::parse($detail->expired_date)->format('Y-m-d') : null;
            $batch = DB::table('stok_batches')
                ->where('obat_id', $detail->obat_id)
                ->where('no_batch', $batchNumber)
                ->when($expiredDate, fn ($query) => $query->whereDate('expired_date', $expiredDate), fn ($query) => $query->whereNull('expired_date'))
                ->first();

            if (! $batch) {
                $batchId = DB::table('stok_batches')->insertGetId([
                    'obat_id' => $detail->obat_id,
                    'no_batch' => $batchNumber,
                    'expired_date' => $expiredDate,
                    'qty' => 0,
                    'harga_beli' => $basePrice,
                    'last_movement_at' => $movementAt,
                    'created_by' => $detail->posted_by,
                    'created_at' => $movementAt,
                    'updated_at' => $movementAt,
                ]);

                $batchQty = 0;
            } else {
                $batchId = $batch->id;
                $batchQty = (float) $batch->qty;
            }

            $saldoBatch = $batchQty + $qty;
            DB::table('stok_batches')->where('id', $batchId)->update([
                'qty' => $saldoBatch,
                'harga_beli' => $basePrice,
                'last_movement_at' => $movementAt,
                'updated_at' => $movementAt,
            ]);

            $saldoTotal = (float) DB::table('stok_batches')
                ->where('obat_id', $detail->obat_id)
                ->sum('qty');

            DB::table('kartu_stok')->insert([
                'obat_id' => $detail->obat_id,
                'stok_batch_id' => $batchId,
                'tanggal_mutasi' => $movementAt,
                'jenis_mutasi' => 'masuk',
                'qty_masuk' => $qty,
                'qty_keluar' => 0,
                'saldo_batch' => $saldoBatch,
                'saldo_total' => $saldoTotal,
                'no_batch' => $batchNumber,
                'expired_date' => $expiredDate,
                'harga_beli' => $basePrice,
                'reference_type' => self::RECEIPT_REFERENCE,
                'reference_id' => $detail->penerimaan_barang_id,
                'reference_detail_id' => $detail->id,
                'nomor_referensi' => $detail->nomor_penerimaan,
                'keterangan' => self::MIGRATION_NOTE . ' (' . number_format((float) $detail->qty_diterima, 2, '.', '') . ' ' . ($detail->satuan_beli ?: 'satuan') . ' x ' . number_format($conversion, 4, '.', '') . ' = ' . number_format($qty, 2, '.', '') . ' ' . ($detail->satuan_stok ?: 'satuan stok') . ')',
                'created_by' => $detail->posted_by,
                'created_at' => $movementAt,
                'updated_at' => $movementAt,
            ]);
        }
    }

    public function down(): void
    {
        $mutations = DB::table('kartu_stok')
            ->where('keterangan', 'like', self::MIGRATION_NOTE . '%')
            ->orderByDesc('id')
            ->get();

        foreach ($mutations as $mutation) {
            if (! $mutation->stok_batch_id) {
                continue;
            }

            $batch = DB::table('stok_batches')->where('id', $mutation->stok_batch_id)->first();

            if (! $batch) {
                continue;
            }

            DB::table('stok_batches')->where('id', $batch->id)->update([
                'qty' => max(0, (float) $batch->qty - (float) $mutation->qty_masuk),
            ]);
        }

        DB::table('kartu_stok')->where('keterangan', 'like', self::MIGRATION_NOTE . '%')->delete();

        DB::table('stok_batches')->where('qty', '<=', 0)->delete();
    }
};
