<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COST_COLUMNS = [
        'branch_id', 'obat_id', 'no_batch', 'expired_date', 'diskon', 'ppn', 'harga_beli', 'biaya_lain',
    ];

    public function up(): void
    {
        Schema::table('margins', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable();
        });

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->foreignId('margin_id')->nullable()->constrained('margins')->nullOnDelete();
            $table->decimal('margin_factor', 8, 3)->nullable();
            // Receipts with identical costs can now carry different selling
            // prices. StockService serializes receipt changes by medicine ID.
            $table->index(self::COST_COLUMNS, 'stok_batches_branch_obat_batch_expired_cost_index');
            $table->dropUnique('stok_batches_branch_obat_batch_expired_cost_unique');
        });

        $this->backfillMatchingBatches();
    }

    private function backfillMatchingBatches(): void
    {
        $defaultPriority = ['sub_golongan', 'main_golongan', 'golongan'];
        $setting = DB::table('margin_settings')->where('key', 'margin_priority')->value('value');
        $savedPriority = json_decode($setting ?? '{}', true)['priority'] ?? [];
        $priority = array_values(array_unique(array_merge(
            array_values(array_intersect($savedPriority, $defaultPriority)),
            $defaultPriority
        )));
        $margins = DB::table('margins')->where('is_active', true)->orderByDesc('id')->get()
            ->groupBy(fn ($margin) => $margin->tingkat.':'.$margin->reference_id);

        // Older batches did not store a margin ID. Only associate a batch when
        // its saved selling price matches the current rule and pricing formula.
        DB::table('stok_batches as batches')
            ->join('master_obats as medicines', 'medicines.id', '=', 'batches.obat_id')
            ->select('batches.*', 'medicines.sub_golongan_id', 'medicines.main_golongan_id', 'medicines.golongan_id')
            ->chunkById(500, function ($batches) use ($margins, $priority) {
                foreach ($batches as $batch) {
                    $margin = null;
                    foreach ($priority as $level) {
                        $referenceId = $batch->{$level.'_id'};
                        if ($referenceId && ($candidate = $margins->get($level.':'.$referenceId)?->first())) {
                            $margin = $candidate;
                            break;
                        }
                    }

                    if (! $margin) {
                        continue;
                    }

                    $basePrice = max(0, (float) $batch->harga_beli)
                        * (1 - min(100, max(0, (float) $batch->diskon)) / 100)
                        * (1 + max(0, (float) $batch->ppn) / 100);
                    $sellingPrice = round($basePrice * (float) $margin->faktor_jual + max(0, (float) $batch->biaya_lain), 2);

                    if ($basePrice <= 0 || abs($sellingPrice - (float) $batch->harga_jual) > 0.005) {
                        continue;
                    }

                    DB::table('stok_batches')->where('id', $batch->id)->update([
                        'margin_id' => $margin->id,
                        'margin_factor' => $margin->faktor_jual,
                    ]);
                    DB::table('margins')->where('id', $margin->id)->whereNull('used_at')
                        ->update(['used_at' => $batch->created_at ?? now()]);
                }
            }, 'batches.id', 'id');
    }

    public function down(): void
    {
        if (DB::table('stok_batches')->whereNotNull('expired_date')->groupBy(self::COST_COLUMNS)
            ->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Tidak dapat rollback: batch dengan biaya sama sudah memiliki lapisan harga berbeda.');
        }

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->unique(self::COST_COLUMNS, 'stok_batches_branch_obat_batch_expired_cost_unique');
            $table->dropIndex('stok_batches_branch_obat_batch_expired_cost_index');
            $table->dropConstrainedForeignId('margin_id');
            $table->dropColumn('margin_factor');
        });

        Schema::table('margins', function (Blueprint $table) {
            $table->dropColumn('used_at');
        });
    }
};
