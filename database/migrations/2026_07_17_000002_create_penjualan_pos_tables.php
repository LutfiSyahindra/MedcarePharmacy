<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('nomor_transaksi', 100)->unique();
            $table->dateTime('tanggal_transaksi');
            $table->string('jenis_transaksi', 40)->default('penjualan_bebas');
            $table->string('status', 30)->default('draft');
            $table->string('payment_status', 30)->default('unpaid');
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('nomor_resep', 100)->nullable();
            $table->string('dokter_name', 150)->nullable();
            $table->string('instansi_name', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('subtotal_gross', 15, 2)->default(0);
            $table->decimal('diskon_item_total', 15, 2)->default(0);
            $table->decimal('diskon_transaksi_percent', 8, 2)->default(0);
            $table->decimal('diskon_transaksi_nominal', 15, 2)->default(0);
            $table->decimal('subtotal_net', 15, 2)->default(0);
            $table->decimal('pajak_percent', 8, 2)->default(0);
            $table->decimal('pajak_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->decimal('sisa_tagihan', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'tanggal_transaksi']);
            $table->index(['branch_id', 'status']);
            $table->index(['jenis_transaksi', 'status']);
        });

        Schema::create('penjualan_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_transaction_id')->constrained('penjualan_transactions')->cascadeOnDelete();
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
            $table->decimal('diskon_percent', 8, 2)->default(0);
            $table->decimal('diskon_nominal', 15, 2)->default(0);
            $table->decimal('subtotal_net', 15, 2)->default(0);
            $table->decimal('pajak_percent', 8, 2)->default(0);
            $table->decimal('pajak_nominal', 15, 2)->default(0);
            $table->decimal('total_line', 15, 2)->default(0);
            $table->json('batch_summary')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['penjualan_transaction_id', 'obat_id'], 'penjualan_details_transaction_obat_index');
        });

        Schema::create('penjualan_transaction_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_transaction_detail_id')
                ->constrained('penjualan_transaction_details', 'id', 'penjualan_batch_detail_fk')
                ->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('obat_id')->nullable()->constrained('master_obats')->nullOnDelete();
            $table->foreignId('stok_batch_id')->nullable()->constrained('stok_batches')->nullOnDelete();
            $table->string('no_batch', 80)->nullable();
            $table->date('expired_date')->nullable();
            $table->decimal('qty_stok', 15, 2)->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('subtotal_gross', 15, 2)->default(0);
            $table->foreignId('kartu_stok_id')->nullable()->constrained('kartu_stok')->nullOnDelete();
            $table->foreignId('cancel_kartu_stok_id')->nullable()->constrained('kartu_stok')->nullOnDelete();
            $table->timestamps();

            $table->index(['stok_batch_id', 'created_at']);
        });

        Schema::create('penjualan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_transaction_id')->constrained('penjualan_transactions')->cascadeOnDelete();
            $table->string('metode', 40);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('reference_no', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['metode', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_payments');
        Schema::dropIfExists('penjualan_transaction_batches');
        Schema::dropIfExists('penjualan_transaction_details');
        Schema::dropIfExists('penjualan_transactions');
    }
};
