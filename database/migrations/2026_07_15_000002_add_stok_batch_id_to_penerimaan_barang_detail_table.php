<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('penerimaan_barang_detail', 'stok_batch_id')) {
            Schema::table('penerimaan_barang_detail', function (Blueprint $table) {
                $table->foreignId('stok_batch_id')
                    ->nullable()
                    ->after('obat_id')
                    ->constrained('stok_batches')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penerimaan_barang_detail', 'stok_batch_id')) {
            Schema::table('penerimaan_barang_detail', function (Blueprint $table) {
                $table->dropConstrainedForeignId('stok_batch_id');
            });
        }
    }
};
