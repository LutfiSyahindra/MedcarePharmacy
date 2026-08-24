<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Repair the historical rack-table typo for fresh installations while
        // leaving existing production databases untouched.
        if (Schema::hasTable('rak_peyimpanans') && ! Schema::hasTable('rak_penyimpanans')) {
            Schema::rename('rak_peyimpanans', 'rak_penyimpanans');
        }

        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('rak_id')->nullable()->constrained('rak_penyimpanans')->nullOnDelete();
            $table->string('nomor', 60)->unique();
            $table->date('tanggal_opname');
            $table->string('status', 40)->default('draft');
            $table->string('transaction_mode', 30)->default('freeze');
            $table->text('catatan')->nullable();
            $table->text('verification_note')->nullable();
            $table->text('approval_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('adjusted_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status'], 'stock_opname_branch_status_index');
            $table->index(['tanggal_opname', 'status'], 'stock_opname_date_status_index');
        });

        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('stok_batch_id')->constrained('stok_batches')->restrictOnDelete();
            $table->foreignId('obat_id')->constrained('master_obats')->restrictOnDelete();
            $table->foreignId('rak_id')->nullable()->constrained('rak_penyimpanans')->nullOnDelete();
            $table->string('kode_obat', 80)->nullable();
            $table->string('nama_obat', 180);
            $table->string('satuan', 80)->nullable();
            $table->string('no_batch', 80);
            $table->date('expired_date')->nullable();
            $table->decimal('stok_sistem_awal', 15, 2)->default(0);
            $table->decimal('stok_sistem_hitung', 15, 2)->nullable();
            $table->decimal('stok_fisik', 15, 2)->nullable();
            $table->decimal('selisih', 15, 2)->nullable();
            $table->text('alasan_selisih')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->foreignId('kartu_stok_id')->nullable()->constrained('kartu_stok')->nullOnDelete();
            $table->timestamps();

            $table->unique(['stock_opname_id', 'stok_batch_id'], 'stock_opname_detail_batch_unique');
            $table->index(['stock_opname_id', 'counted_at'], 'stock_opname_detail_count_index');
        });

        Schema::create('stock_opname_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('stock_opname_detail_id')->nullable()->constrained('stock_opname_details')->nullOnDelete();
            $table->foreignId('kartu_stok_id')->constrained('kartu_stok')->cascadeOnDelete();
            $table->foreignId('stok_batch_id')->nullable()->constrained('stok_batches')->nullOnDelete();
            $table->string('jenis_mutasi', 40);
            $table->decimal('qty_masuk', 15, 2)->default(0);
            $table->decimal('qty_keluar', 15, 2)->default(0);
            $table->decimal('saldo_batch', 15, 2)->default(0);
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->unique('kartu_stok_id', 'stock_opname_movement_card_unique');
            $table->index(['stock_opname_id', 'occurred_at'], 'stock_opname_movement_time_index');
        });

        Schema::create('stock_opname_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->string('action', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index(['stock_opname_id', 'performed_at'], 'stock_opname_log_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_logs');
        Schema::dropIfExists('stock_opname_movements');
        Schema::dropIfExists('stock_opname_details');
        Schema::dropIfExists('stock_opnames');
    }
};
