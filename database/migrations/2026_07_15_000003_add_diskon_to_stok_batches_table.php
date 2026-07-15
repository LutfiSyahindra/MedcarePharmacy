<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'stok_batches_obat_batch_expired_unique';
    private const NEW_UNIQUE = 'stok_batches_obat_batch_expired_diskon_unique';

    public function up(): void
    {
        if (! Schema::hasColumn('stok_batches', 'diskon')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->decimal('diskon', 8, 2)->default(0)->after('harga_beli');
            });
        }

        $this->dropUniqueSafely(self::OLD_UNIQUE);

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->unique(['obat_id', 'no_batch', 'expired_date', 'diskon'], self::NEW_UNIQUE);
        });
    }

    public function down(): void
    {
        $this->dropUniqueSafely(self::NEW_UNIQUE);

        if (Schema::hasColumn('stok_batches', 'diskon')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->dropColumn('diskon');
            });
        }

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->unique(['obat_id', 'no_batch', 'expired_date'], self::OLD_UNIQUE);
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
