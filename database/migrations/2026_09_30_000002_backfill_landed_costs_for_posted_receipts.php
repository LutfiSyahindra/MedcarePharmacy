<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->columnsAreAvailable()) {
            return;
        }

        DB::table('penerimaan_barang')
            ->where('status', 'posted')
            ->where('biaya_lain', '>', 0)
            ->orderBy('id')
            ->select(['id', 'biaya_lain'])
            ->chunkById(100, function ($receipts) {
                foreach ($receipts as $receipt) {
                    $this->backfillReceipt((int) $receipt->id, (float) $receipt->biaya_lain);
                }
            });
    }

    public function down(): void
    {
        if (! $this->columnsAreAvailable()) {
            return;
        }

        DB::table('penerimaan_barang_detail')
            ->where('biaya_lain_stok', '>', 0)
            ->orderBy('id')
            ->select(['id', 'stok_batch_id'])
            ->chunkById(200, function ($details) {
                foreach ($details as $detail) {
                    DB::table('penerimaan_barang_detail')
                        ->where('id', $detail->id)
                        ->update([
                            'alokasi_biaya_lain' => 0,
                            'biaya_lain_stok' => 0,
                            'updated_at' => now(),
                        ]);

                    if ($detail->stok_batch_id && $this->batchHasSingleReceiptDetail((int) $detail->stok_batch_id)) {
                        DB::table('stok_batches')
                            ->where('id', $detail->stok_batch_id)
                            ->where('biaya_lain', '>', 0)
                            ->update([
                                'biaya_lain' => 0,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });
    }

    private function backfillReceipt(int $receiptId, float $totalOtherCost): void
    {
        $details = DB::table('penerimaan_barang_detail')
            ->where('penerimaan_barang_id', $receiptId)
            ->where('qty_diterima', '>', 0)
            ->orderBy('id')
            ->get([
                'id',
                'stok_batch_id',
                'qty_diterima',
                'qty_diterima_stok',
                'konversi_satuan',
                'harga_beli',
                'harga_beli_stok',
                'alokasi_biaya_lain',
                'biaya_lain_stok',
            ]);
        $remainingCost = round(max(0, $totalOtherCost), 2);
        $remainingQty = (float) $details->sum('qty_diterima');

        foreach ($details as $index => $detail) {
            if ((float) $detail->biaya_lain_stok > 0 || $remainingQty <= 0) {
                $remainingCost = round(max(0, $remainingCost - (float) $detail->alokasi_biaya_lain), 2);
                $remainingQty = max(0, $remainingQty - (float) $detail->qty_diterima);

                continue;
            }

            $qty = (float) $detail->qty_diterima;
            $allocation = $index === $details->count() - 1
                ? $remainingCost
                : round($remainingCost * $qty / $remainingQty, 2);
            $stockQty = (float) $detail->qty_diterima_stok;

            if ($stockQty <= 0) {
                $stockQty = $qty * max(1, (float) $detail->konversi_satuan);
            }

            $unitOtherCost = $stockQty > 0 ? round($allocation / $stockQty, 2) : 0;
            $currentStockCost = (float) $detail->harga_beli_stok;

            if ($currentStockCost <= 0) {
                $conversion = max(1, (float) $detail->konversi_satuan);
                $currentStockCost = (float) $detail->harga_beli / $conversion;
            }

            $currentStockCost = round($currentStockCost, 2);

            DB::table('penerimaan_barang_detail')
                ->where('id', $detail->id)
                ->update([
                    'harga_beli_stok' => $currentStockCost,
                    'alokasi_biaya_lain' => $allocation,
                    'biaya_lain_stok' => $unitOtherCost,
                    'updated_at' => now(),
                ]);

            if ($detail->stok_batch_id && $this->batchHasSingleReceiptDetail((int) $detail->stok_batch_id)) {
                DB::table('stok_batches')
                    ->where('id', $detail->stok_batch_id)
                    ->where('biaya_lain', '<=', 0)
                    ->update([
                        'harga_beli' => $currentStockCost,
                        'biaya_lain' => $unitOtherCost,
                        'updated_at' => now(),
                    ]);
            }

            $remainingCost = round(max(0, $remainingCost - $allocation), 2);
            $remainingQty = max(0, $remainingQty - $qty);
        }
    }

    private function batchHasSingleReceiptDetail(int $batchId): bool
    {
        return DB::table('penerimaan_barang_detail')
            ->where('stok_batch_id', $batchId)
            ->limit(2)
            ->get(['id'])
            ->count() === 1;
    }

    private function columnsAreAvailable(): bool
    {
        return Schema::hasColumns('penerimaan_barang_detail', [
            'alokasi_biaya_lain',
            'biaya_lain_stok',
        ]) && Schema::hasColumn('stok_batches', 'biaya_lain');
    }
};
