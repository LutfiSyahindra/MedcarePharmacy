<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->foreignId('stok_batch_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('stock_opname_details')->whereNull('stok_batch_id')->delete();

        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->foreignId('stok_batch_id')->nullable(false)->change();
        });
    }
};
