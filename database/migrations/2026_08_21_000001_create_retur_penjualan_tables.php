<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('penjualan_transaction_id')->constrained('penjualan_transactions')->cascadeOnDelete();
            $table->string('nomor_retur', 100)->unique();
            $table->date('tanggal_retur');
            $table->string('status', 30)->default('draft');
            $table->string('refund_method', 40)->nullable();
            $table->string('refund_reference', 120)->nullable();
            $table->unsignedInteger('total_item')->default(0);
            $table->decimal('total_qty', 15, 2)->default(0);
            $table->decimal('subtotal_gross', 15, 2)->default(0);
            $table->decimal('diskon_item_total', 15, 2)->default(0);
            $table->decimal('diskon_transaksi_total', 15, 2)->default(0);
            $table->decimal('pajak_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('alasan')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'tanggal_retur']);
            $table->index(['branch_id', 'status']);
            $table->index(['penjualan_transaction_id', 'status'], 'retur_penjualan_transaction_status_index');
        });

        Schema::create('retur_penjualan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_penjualan_id')->constrained('retur_penjualan')->cascadeOnDelete();
            $table->foreignId('penjualan_transaction_detail_id')
                ->constrained('penjualan_transaction_details', 'id', 'retur_penjualan_detail_sale_detail_fk')
                ->cascadeOnDelete();
            $table->foreignId('obat_id')->nullable()->constrained('master_obats')->nullOnDelete();
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->nullOnDelete();
            $table->string('kode_obat', 80)->nullable();
            $table->string('nama_obat', 180);
            $table->string('satuan_jual', 80)->nullable();
            $table->string('satuan_stok', 80)->nullable();
            $table->decimal('konversi', 15, 4)->default(1);
            $table->decimal('qty_jual', 15, 2)->default(0);
            $table->decimal('qty_stok', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('subtotal_gross', 15, 2)->default(0);
            $table->decimal('diskon_item_nominal', 15, 2)->default(0);
            $table->decimal('diskon_transaksi_nominal', 15, 2)->default(0);
            $table->decimal('pajak_nominal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('alasan_item')->nullable();
            $table->timestamps();

            $table->index(['retur_penjualan_id', 'obat_id']);
            $table->index('penjualan_transaction_detail_id', 'retur_penjualan_sale_detail_index');
        });

        Schema::create('retur_penjualan_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_penjualan_detail_id')
                ->constrained('retur_penjualan_details', 'id', 'retur_penjualan_batch_detail_fk')
                ->cascadeOnDelete();
            $table->foreignId('penjualan_transaction_batch_id')
                ->constrained('penjualan_transaction_batches', 'id', 'retur_penjualan_batch_sale_batch_fk')
                ->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('obat_id')->nullable()->constrained('master_obats')->nullOnDelete();
            $table->foreignId('stok_batch_id')->nullable()->constrained('stok_batches')->nullOnDelete();
            $table->string('no_batch', 80)->nullable();
            $table->date('expired_date')->nullable();
            $table->decimal('qty_stok', 15, 2)->default(0);
            $table->foreignId('kartu_stok_id')->nullable()->constrained('kartu_stok')->nullOnDelete();
            $table->foreignId('cancel_kartu_stok_id')->nullable()->constrained('kartu_stok')->nullOnDelete();
            $table->timestamps();

            $table->index(['penjualan_transaction_batch_id', 'created_at'], 'retur_penjualan_sale_batch_index');
            $table->index(['stok_batch_id', 'created_at'], 'retur_penjualan_stock_batch_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_penjualan_batches');
        Schema::dropIfExists('retur_penjualan_details');
        Schema::dropIfExists('retur_penjualan');
    }
};
