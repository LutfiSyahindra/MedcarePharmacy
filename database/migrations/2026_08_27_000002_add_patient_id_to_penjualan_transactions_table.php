<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->foreignId('patient_id')
                ->nullable()
                ->after('customer_phone')
                ->constrained('patients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
        });
    }
};
