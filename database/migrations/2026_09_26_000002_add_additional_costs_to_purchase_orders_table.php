<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('biaya_asuransi', 15, 2)
                ->default(0)
                ->after('total_estimasi');
            $table->decimal('biaya_pengiriman', 15, 2)
                ->default(0)
                ->after('biaya_asuransi');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['biaya_asuransi', 'biaya_pengiriman']);
        });
    }
};
