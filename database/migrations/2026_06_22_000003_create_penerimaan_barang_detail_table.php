<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_barang_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_barang_id')->constrained('penerimaan_barang')->cascadeOnDelete();
            $table->foreignId('purchase_order_detail_id')->nullable()->constrained('purchase_order_details')->nullOnDelete();
            $table->foreignId('obat_id')->constrained('master_obats')->cascadeOnDelete();
            $table->decimal('qty_po', 15, 2)->default(0);
            $table->decimal('qty_diterima', 15, 2)->default(0);
            $table->string('no_batch', 80)->nullable();
            $table->date('expired_date')->nullable();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('diskon', 8, 2)->default(0);
            $table->decimal('ppn', 8, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('nilai_diskon', 15, 2)->default(0);
            $table->decimal('nilai_ppn', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang_detail');
    }
};
