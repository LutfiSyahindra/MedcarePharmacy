<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('master_obats')->cascadeOnDelete();
            $table->string('no_batch', 80);
            $table->date('expired_date')->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['obat_id', 'no_batch', 'expired_date'], 'stok_batches_obat_batch_expired_unique');
            $table->index(['obat_id', 'expired_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_batches');
    }
};
