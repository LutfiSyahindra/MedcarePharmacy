<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->decimal('supplier_compensation_discount', 15, 2)
                ->default(0)
                ->after('biaya_lain');
        });

        Schema::table('retur_pembelian_compensations', function (Blueprint $table) {
            $table->foreignId('penerimaan_barang_id')
                ->nullable()
                ->after('retur_pembelian_id')
                ->constrained('penerimaan_barang')
                ->nullOnDelete();
        });

        Schema::create('penerimaan_supplier_compensation_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_barang_id');
            $table->unsignedBigInteger('retur_pembelian_id');
            $table->unsignedBigInteger('retur_pembelian_compensation_id')->nullable();
            $table->decimal('nominal', 15, 2);
            $table->timestamps();

            $table->foreign('penerimaan_barang_id', 'recv_comp_alloc_receipt_fk')
                ->references('id')
                ->on('penerimaan_barang')
                ->cascadeOnDelete();
            $table->foreign('retur_pembelian_id', 'recv_comp_alloc_return_fk')
                ->references('id')
                ->on('retur_pembelian')
                ->cascadeOnDelete();
            $table->foreign('retur_pembelian_compensation_id', 'recv_comp_alloc_entry_fk')
                ->references('id')
                ->on('retur_pembelian_compensations')
                ->nullOnDelete();

            $table->unique(
                ['penerimaan_barang_id', 'retur_pembelian_id'],
                'recv_comp_alloc_receipt_return_unique'
            );
            $table->index('retur_pembelian_compensation_id', 'recv_comp_alloc_entry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_supplier_compensation_allocations');

        Schema::table('retur_pembelian_compensations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penerimaan_barang_id');
        });

        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->dropColumn('supplier_compensation_discount');
        });
    }
};
