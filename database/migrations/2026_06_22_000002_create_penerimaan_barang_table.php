<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_barang', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penerimaan', 60)->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('distributor_id')->constrained('distributors')->cascadeOnDelete();
            $table->string('nomor_faktur', 100);
            $table->string('nomor_surat_jalan', 100)->nullable();
            $table->date('tanggal_penerimaan');
            $table->integer('total_barang')->default(0);
            $table->decimal('total_qty', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_diskon', 15, 2)->default(0);
            $table->decimal('total_ppn', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang');
    }
};
