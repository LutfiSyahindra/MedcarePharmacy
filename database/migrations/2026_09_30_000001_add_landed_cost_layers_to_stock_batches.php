<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'stok_batches_branch_obat_batch_expired_diskon_ppn_unique';

    private const NEW_UNIQUE = 'stok_batches_branch_obat_batch_expired_cost_unique';

    public function up(): void
    {
        Schema::table('penerimaan_barang_detail', function (Blueprint $table) {
            if (! Schema::hasColumn('penerimaan_barang_detail', 'alokasi_biaya_lain')) {
                $table->decimal('alokasi_biaya_lain', 15, 2)->default(0)->after('harga_beli_stok');
            }

            if (! Schema::hasColumn('penerimaan_barang_detail', 'biaya_lain_stok')) {
                $table->decimal('biaya_lain_stok', 15, 2)->default(0)->after('alokasi_biaya_lain');
            }
        });

        Schema::table('stok_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('stok_batches', 'biaya_lain')) {
                $table->decimal('biaya_lain', 15, 2)->default(0)->after('harga_beli');
            }
        });

        $this->dropUniqueSafely(self::OLD_UNIQUE);

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->unique(
                ['branch_id', 'obat_id', 'no_batch', 'expired_date', 'diskon', 'ppn', 'harga_beli', 'biaya_lain'],
                self::NEW_UNIQUE
            );
        });
    }

    public function down(): void
    {
        $this->dropUniqueSafely(self::NEW_UNIQUE);

        if (Schema::hasColumn('stok_batches', 'biaya_lain')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->dropColumn('biaya_lain');
            });
        }

        Schema::table('penerimaan_barang_detail', function (Blueprint $table) {
            $columns = collect(['alokasi_biaya_lain', 'biaya_lain_stok'])
                ->filter(fn (string $column) => Schema::hasColumn('penerimaan_barang_detail', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        try {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->unique(
                    ['branch_id', 'obat_id', 'no_batch', 'expired_date', 'diskon', 'ppn'],
                    self::OLD_UNIQUE
                );
            });
        } catch (\Throwable) {
            // Cost layers can make the former unique index impossible to restore.
        }
    }

    private function dropUniqueSafely(string $indexName): void
    {
        try {
            Schema::table('stok_batches', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Throwable) {
            // The database may already have the target index shape.
        }
    }
};
