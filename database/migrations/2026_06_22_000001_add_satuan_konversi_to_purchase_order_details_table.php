<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_details', 'satuan_konversi')) {
                $table->foreignId('satuan_konversi')
                    ->nullable()
                    ->after('subtotal')
                    ->constrained('obat_satuan_conversions')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_details', 'satuan_konversi')) {
                $table->dropConstrainedForeignId('satuan_konversi');
            }
        });
    }
};
