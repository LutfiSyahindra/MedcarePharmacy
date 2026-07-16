<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_retur', 60)->unique();
            $table->foreignId('penerimaan_barang_id')->constrained('penerimaan_barang')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('distributor_id')->constrained('distributors')->cascadeOnDelete();
            $table->date('tanggal_retur');
            $table->string('nomor_referensi_supplier', 100)->nullable();
            $table->integer('total_barang')->default(0);
            $table->decimal('total_qty', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_diskon', 15, 2)->default(0);
            $table->decimal('total_ppn', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->text('alasan')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['penerimaan_barang_id', 'status']);
            $table->index(['purchase_order_id', 'status']);
            $table->index(['tanggal_retur', 'status']);
        });

        Schema::create('retur_pembelian_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_pembelian_id')->constrained('retur_pembelian')->cascadeOnDelete();
            $table->foreignId('penerimaan_barang_detail_id')->nullable()->constrained('penerimaan_barang_detail')->nullOnDelete();
            $table->foreignId('purchase_order_detail_id')->nullable()->constrained('purchase_order_details')->nullOnDelete();
            $table->foreignId('obat_id')->constrained('master_obats')->cascadeOnDelete();
            $table->foreignId('stok_batch_id')->nullable()->constrained('stok_batches')->nullOnDelete();
            $table->decimal('qty_diterima', 15, 2)->default(0);
            $table->decimal('qty_retur', 15, 2)->default(0);
            $table->decimal('qty_retur_stok', 15, 2)->default(0);
            $table->decimal('konversi_satuan', 15, 4)->default(1);
            $table->string('satuan_beli', 50)->nullable();
            $table->string('satuan_stok', 50)->nullable();
            $table->string('no_batch', 80)->nullable();
            $table->date('expired_date')->nullable();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_beli_stok', 15, 2)->default(0);
            $table->decimal('diskon', 8, 2)->default(0);
            $table->decimal('ppn', 8, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('nilai_diskon', 15, 2)->default(0);
            $table->decimal('nilai_ppn', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('alasan_item')->nullable();
            $table->timestamps();

            $table->index(['retur_pembelian_id', 'obat_id']);
            $table->index(['penerimaan_barang_detail_id']);
            $table->index(['stok_batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_detail');
        Schema::dropIfExists('retur_pembelian');
    }
};
