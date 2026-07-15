<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_harga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('master_obats')->cascadeOnDelete();
            $table->foreignId('stok_batch_id')->nullable()->constrained('stok_batches')->nullOnDelete();
            $table->decimal('harga_jual_lama', 15, 2)->default(0);
            $table->decimal('harga_jual_baru', 15, 2)->default(0);
            $table->text('alasan')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['obat_id', 'created_at']);
            $table->index(['stok_batch_id', 'created_at']);
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_harga');
    }
};
