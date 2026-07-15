<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'stok_batches_obat_batch_expired_diskon_unique';
    private const NEW_UNIQUE = 'stok_batches_obat_batch_expired_diskon_ppn_unique';

    public function up(): void
    {
        if (! Schema::hasColumn('stok_batches', 'ppn')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->decimal('ppn', 8, 2)->default(0)->after('diskon');
            });
        }

        $this->backfillPpnFromReceiptDetails();
        $this->dropUniqueSafely(self::OLD_UNIQUE);

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->unique(['obat_id', 'no_batch', 'expired_date', 'diskon', 'ppn'], self::NEW_UNIQUE);
        });
    }

    public function down(): void
    {
        $this->dropUniqueSafely(self::NEW_UNIQUE);

        if (Schema::hasColumn('stok_batches', 'ppn')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->dropColumn('ppn');
            });
        }

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->unique(['obat_id', 'no_batch', 'expired_date', 'diskon'], self::OLD_UNIQUE);
        });
    }

    private function backfillPpnFromReceiptDetails(): void
    {
        if (! Schema::hasTable('penerimaan_barang_detail') || ! Schema::hasColumn('penerimaan_barang_detail', 'stok_batch_id')) {
            return;
        }

        DB::table('penerimaan_barang_detail')
            ->select('stok_batch_id', DB::raw('MAX(ppn) as ppn'))
            ->whereNotNull('stok_batch_id')
            ->groupBy('stok_batch_id')
            ->orderBy('stok_batch_id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('stok_batches')
                        ->where('id', $row->stok_batch_id)
                        ->update(['ppn' => (float) ($row->ppn ?? 0)]);
                }
            });
    }

    private function dropUniqueSafely(string $indexName): void
    {
        try {
            Schema::table('stok_batches', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Throwable) {
            // The target database may already have the desired index shape.
        }
    }
};
