<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->json('posting_summary')->nullable()->after('approval_note');
        });

        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->decimal('hpp', 15, 2)->default(0)->after('expired_date');
            $table->decimal('mutasi_masuk', 15, 2)->default(0)->after('alasan_selisih');
            $table->decimal('mutasi_keluar', 15, 2)->default(0)->after('mutasi_masuk');
            $table->decimal('stok_sistem_validasi', 15, 2)->nullable()->after('mutasi_keluar');
            $table->decimal('stok_target_validasi', 15, 2)->nullable()->after('stok_sistem_validasi');
            $table->decimal('selisih_validasi', 15, 2)->nullable()->after('stok_target_validasi');
        });
    }

    public function down(): void
    {
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->dropColumn([
                'hpp', 'mutasi_masuk', 'mutasi_keluar', 'stok_sistem_validasi',
                'stok_target_validasi', 'selisih_validasi',
            ]);
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn('posting_summary');
        });
    }
};
