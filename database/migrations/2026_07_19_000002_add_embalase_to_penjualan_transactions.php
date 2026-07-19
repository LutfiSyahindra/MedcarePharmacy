<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->decimal('embalase', 15, 2)->default(0)->after('subtotal_net');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->dropColumn('embalase');
        });
    }
};
