<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('master_obats')->cascadeOnDelete();
            $table->foreignId('stok_batch_id')->nullable()->constrained('stok_batches')->nullOnDelete();
            $table->timestamp('tanggal_mutasi');
            $table->string('jenis_mutasi', 40);
            $table->decimal('qty_masuk', 15, 2)->default(0);
            $table->decimal('qty_keluar', 15, 2)->default(0);
            $table->decimal('saldo_batch', 15, 2)->default(0);
            $table->decimal('saldo_total', 15, 2)->default(0);
            $table->string('no_batch', 80)->nullable();
            $table->date('expired_date')->nullable();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('reference_detail_id')->nullable();
            $table->string('nomor_referensi', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['obat_id', 'tanggal_mutasi']);
            $table->index(['stok_batch_id', 'tanggal_mutasi']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('jenis_mutasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_stok');
    }
};
