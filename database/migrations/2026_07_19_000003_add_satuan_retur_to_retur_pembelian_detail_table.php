<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retur_pembelian_detail', function (Blueprint $table) {
            $table->foreignId('satuan_retur_id')
                ->nullable()
                ->after('konversi_satuan')
                ->constrained('satuans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('retur_pembelian_detail', function (Blueprint $table) {
            $table->dropConstrainedForeignId('satuan_retur_id');
        });
    }
};
