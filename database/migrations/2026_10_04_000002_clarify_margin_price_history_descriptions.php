<?php

use App\Models\MarginsModel;
use App\Models\Menu\Stok\RiwayatHargaModel;
use App\Support\MarginPriceHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $marginLabels = [];

        RiwayatHargaModel::query()->with(['obat', 'batch'])
            ->where('alasan', 'like', 'Perubahan margin #% diterapkan pada produk yang telah menggunakan margin tersebut.%')
            ->chunkById(200, function ($histories) use (&$marginLabels) {
                foreach ($histories as $history) {
                    if (! preg_match('/^Perubahan margin #(\d+) diterapkan pada produk yang telah menggunakan margin tersebut\.$/u', trim($history->alasan), $matches)) {
                        continue;
                    }

                    $marginId = (int) $matches[1];
                    $marginLabel = $marginLabels[$marginId] ??= MarginPriceHistory::marginLabel(MarginsModel::find($marginId));

                    // Legacy notes did not preserve factors. Use the recorded
                    // prices, without inferring past factors from today's stock.
                    DB::table('riwayat_harga')->where('id', $history->id)->where('alasan', $history->alasan)->update([
                        'alasan' => MarginPriceHistory::describe(
                            $marginLabel,
                            $history->obat?->nama_obat ?? 'Produk tidak lagi tersedia',
                            $history->batch?->no_batch,
                            (float) $history->harga_jual_lama,
                            (float) $history->harga_jual_baru
                        ),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Keep the more descriptive audit notes when rolling back code.
    }
};
