<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('stok_batches', 'harga_jual')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->decimal('harga_jual', 15, 2)->default(0)->after('harga_beli');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stok_batches', 'harga_jual')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->dropColumn('harga_jual');
            });
        }
    }
};
