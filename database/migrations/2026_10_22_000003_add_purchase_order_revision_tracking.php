<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedInteger('revision')->default(0);
        });
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->boolean('requires_po_review')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->dropColumn('requires_po_review');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('revision');
        });
    }
};
