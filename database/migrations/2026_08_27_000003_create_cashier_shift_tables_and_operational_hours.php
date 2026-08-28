<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->time('operational_start_time')->default('00:00:00')->after('is_active');
            $table->time('operational_end_time')->default('00:00:00')->after('operational_start_time');
            $table->string('operational_timezone', 60)->default('Asia/Jakarta')->after('operational_end_time');
        });

        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('shift_number', 100)->unique();
            $table->string('status', 20)->default('open');
            $table->decimal('opening_amount', 15, 2)->default(0);
            $table->decimal('cash_sales', 15, 2)->default(0);
            $table->decimal('cash_in_total', 15, 2)->default(0);
            $table->decimal('cash_out_total', 15, 2)->default(0);
            $table->decimal('expected_cash', 15, 2)->default(0);
            $table->decimal('actual_cash', 15, 2)->nullable();
            $table->decimal('cash_difference', 15, 2)->nullable();
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['opened_at', 'closed_at']);
        });

        Schema::create('cashier_cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_shift_id')->constrained('cashier_shifts')->cascadeOnDelete();
            $table->string('type', 20);
            $table->decimal('amount', 15, 2);
            $table->string('description', 500);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['cashier_shift_id', 'type']);
            $table->index('occurred_at');
        });

        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->foreignId('cashier_shift_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('cashier_shifts')
                ->nullOnDelete();
            $table->index(['cashier_shift_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_transactions', function (Blueprint $table) {
            $table->dropIndex(['cashier_shift_id', 'status']);
            $table->dropConstrainedForeignId('cashier_shift_id');
        });

        Schema::dropIfExists('cashier_cash_movements');
        Schema::dropIfExists('cashier_shifts');

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'operational_start_time',
                'operational_end_time',
                'operational_timezone',
            ]);
        });
    }
};
