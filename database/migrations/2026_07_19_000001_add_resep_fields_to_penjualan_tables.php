<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->date('tanggal_resep')->nullable()->after('nomor_resep');
            $table->string('asal_resep', 150)->nullable()->after('dokter_name');
        });

        Schema::table('penjualan_transaction_details', function (Blueprint $table) {
            $table->string('aturan_pakai', 255)->nullable()->after('keterangan');
            $table->string('waktu_konsumsi', 80)->nullable()->after('aturan_pakai');
            $table->unsignedSmallInteger('durasi_hari')->nullable()->after('waktu_konsumsi');
            $table->string('racikan_group', 80)->nullable()->after('durasi_hari');
            $table->string('dosis_komponen', 100)->nullable()->after('racikan_group');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_transaction_details', function (Blueprint $table) {
            $table->dropColumn([
                'aturan_pakai',
                'waktu_konsumsi',
                'durasi_hari',
                'racikan_group',
                'dosis_komponen',
            ]);
        });

        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->dropColumn(['tanggal_resep', 'asal_resep']);
        });
    }
};
