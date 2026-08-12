<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_transaction_details', function (Blueprint $table) {
            $table->string('bentuk_racikan', 80)->nullable()->after('racikan_group');
            $table->decimal('jumlah_racikan', 12, 2)->nullable()->after('bentuk_racikan');
            $table->decimal('jumlah_ambil_resep', 12, 2)->nullable()->after('jumlah_racikan');
            $table->string('signa_1', 50)->nullable()->after('jumlah_ambil_resep');
            $table->string('signa_2', 50)->nullable()->after('signa_1');
            $table->decimal('embalase_racikan', 15, 2)->default(0)->after('signa_2');
            $table->string('kekuatan_obat', 100)->nullable()->after('dosis_komponen');
            $table->decimal('jumlah_resep', 12, 2)->nullable()->after('kekuatan_obat');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_transaction_details', function (Blueprint $table) {
            $table->dropColumn([
                'bentuk_racikan',
                'jumlah_racikan',
                'jumlah_ambil_resep',
                'signa_1',
                'signa_2',
                'embalase_racikan',
                'kekuatan_obat',
                'jumlah_resep',
            ]);
        });
    }
};
