<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retur_pembelian', function (Blueprint $table) {
            $table->boolean('expects_compensation')->default(true)->after('grand_total');
            $table->date('compensation_due_date')->nullable()->after('expects_compensation');
            $table->text('compensation_notes')->nullable()->after('compensation_due_date');

            $table->index(['expects_compensation', 'compensation_due_date'], 'retur_compensation_due_index');
        });

        Schema::create('retur_pembelian_compensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_pembelian_id')
                ->constrained('retur_pembelian')
                ->cascadeOnDelete();
            $table->date('tanggal_realisasi');
            $table->string('jenis', 40);
            $table->decimal('nominal', 15, 2);
            $table->string('nomor_referensi', 120)->nullable();
            $table->string('nomor_faktur', 120)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['retur_pembelian_id', 'cancelled_at'], 'retur_compensation_active_index');
            $table->index(['tanggal_realisasi', 'jenis'], 'retur_compensation_date_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_compensations');

        Schema::table('retur_pembelian', function (Blueprint $table) {
            $table->dropIndex('retur_compensation_due_index');
            $table->dropColumn([
                'expects_compensation',
                'compensation_due_date',
                'compensation_notes',
            ]);
        });
    }
};
