<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumns('penerimaan_barang_detail', [
            'alokasi_biaya_lain',
            'biaya_lain_stok',
        ]) || ! Schema::hasColumn('stok_batches', 'biaya_lain')) {
            return;
        }

        DB::table('penerimaan_barang_detail as detail')
            ->join('penerimaan_barang as receipt', 'receipt.id', '=', 'detail.penerimaan_barang_id')
            ->where('receipt.status', 'posted')
            ->where('detail.alokasi_biaya_lain', '>', 0)
            ->orderBy('detail.id')
            ->select([
                'detail.id',
                'detail.stok_batch_id',
                'detail.qty_diterima',
                'detail.qty_diterima_stok',
                'detail.konversi_satuan',
                'detail.harga_beli',
                'detail.subtotal',
                'detail.nilai_diskon',
                'detail.diskon',
                'detail.biaya_lain_stok',
                'receipt.diskon_untuk',
            ])
            ->chunkById(200, function ($details) {
                foreach ($details as $detail) {
                    $stockCost = $this->stockCostExcludingOtherCost($detail);

                    DB::table('penerimaan_barang_detail')
                        ->where('id', $detail->id)
                        ->update([
                            'harga_beli_stok' => $stockCost,
                            'updated_at' => now(),
                        ]);

                    if ($detail->stok_batch_id && $this->batchHasSingleReceiptDetail((int) $detail->stok_batch_id)) {
                        DB::table('stok_batches')
                            ->where('id', $detail->stok_batch_id)
                            ->update([
                                'harga_beli' => $stockCost,
                                'biaya_lain' => max(0, round((float) $detail->biaya_lain_stok, 2)),
                                'updated_at' => now(),
                            ]);
                    }
                }
            }, 'detail.id', 'id');
    }

    public function down(): void
    {
        // HPP yang sudah dikoreksi tidak dikembalikan ke klasifikasi yang keliru.
    }

    private function stockCostExcludingOtherCost(object $detail): float
    {
        $stockQty = (float) $detail->qty_diterima_stok;

        if ($stockQty <= 0) {
            $stockQty = (float) $detail->qty_diterima * max(1, (float) $detail->konversi_satuan);
        }

        if ($stockQty <= 0) {
            return 0;
        }

        $subtotal = max(0, (float) $detail->subtotal);

        if ($subtotal <= 0) {
            $subtotal = max(0, (float) $detail->qty_diterima * (float) $detail->harga_beli);
        }

        if (($detail->diskon_untuk ?: 'pasien') === 'pasien') {
            $discountValue = (float) $detail->nilai_diskon;

            if ($discountValue <= 0) {
                $discountValue = $subtotal * min(100, max(0, (float) $detail->diskon)) / 100;
            }

            $subtotal = max(0, $subtotal - $discountValue);
        }

        return round($subtotal / $stockQty, 2);
    }

    private function batchHasSingleReceiptDetail(int $batchId): bool
    {
        return DB::table('penerimaan_barang_detail')
            ->where('stok_batch_id', $batchId)
            ->limit(2)
            ->get(['id'])
            ->count() === 1;
    }
};
