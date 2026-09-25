<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->index(
                ['branch_id', 'status', 'tanggal_transaksi'],
                'sales_branch_status_date_index'
            );
        });

        Schema::table('retur_penjualan', function (Blueprint $table) {
            $table->index(
                ['branch_id', 'status', 'tanggal_retur'],
                'sales_returns_branch_status_date_index'
            );
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(
                ['branch_id', 'status', 'created_at'],
                'purchase_orders_branch_status_created_index'
            );
        });

        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->index(
                ['purchase_order_id', 'status', 'posted_at'],
                'receipts_order_status_posted_index'
            );
        });

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->index(
                ['branch_id', 'expired_date', 'obat_id'],
                'stock_batches_branch_expiry_medicine_index'
            );
        });

        Schema::table('kartu_stok', function (Blueprint $table) {
            $table->index(
                ['branch_id', 'tanggal_mutasi', 'jenis_mutasi'],
                'stock_cards_branch_date_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('kartu_stok', function (Blueprint $table) {
            $table->dropIndex('stock_cards_branch_date_type_index');
        });

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->dropIndex('stock_batches_branch_expiry_medicine_index');
        });

        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->dropIndex('receipts_order_status_posted_index');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('purchase_orders_branch_status_created_index');
        });

        Schema::table('retur_penjualan', function (Blueprint $table) {
            $table->dropIndex('sales_returns_branch_status_date_index');
        });

        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->dropIndex('sales_branch_status_date_index');
        });
    }
};
