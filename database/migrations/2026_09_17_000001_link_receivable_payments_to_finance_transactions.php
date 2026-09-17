<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('penjualan_payments', 'finance_transaction_id')) {
            Schema::table('penjualan_payments', function (Blueprint $table) {
                $table->foreignId('finance_transaction_id')
                    ->nullable()
                    ->after('penjualan_transaction_id')
                    ->constrained('finance_transactions')
                    ->nullOnDelete();
                $table->index(['finance_transaction_id', 'paid_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penjualan_payments', 'finance_transaction_id')) {
            Schema::table('penjualan_payments', function (Blueprint $table) {
                $table->dropIndex(['finance_transaction_id', 'paid_at']);
                $table->dropConstrainedForeignId('finance_transaction_id');
            });
        }
    }
};
