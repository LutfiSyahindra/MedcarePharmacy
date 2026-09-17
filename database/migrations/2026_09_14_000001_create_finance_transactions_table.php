<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finance_categories')) {
            Schema::create('finance_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
                $table->string('code', 50);
                $table->string('name', 120);
                $table->string('type', 20);
                $table->string('group', 60)->nullable();
                $table->boolean('is_operational')->default(false);
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['branch_id', 'code']);
                $table->index(['branch_id', 'type', 'is_active']);
            });
        }

        if (! Schema::hasTable('finance_accounts')) {
            Schema::create('finance_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->string('code', 40);
                $table->string('name', 120);
                $table->string('type', 20);
                $table->string('bank_name', 100)->nullable();
                $table->string('account_number', 100)->nullable();
                $table->string('account_holder', 120)->nullable();
                $table->decimal('opening_balance', 18, 2)->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['branch_id', 'code']);
                $table->index(['branch_id', 'type', 'is_active']);
            });
        }

        if (! Schema::hasTable('finance_transactions')) {
            Schema::create('finance_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('finance_categories')->nullOnDelete();
                $table->string('number', 80)->unique();
                $table->dateTime('transaction_date');
                $table->string('type', 40);
                $table->string('status', 20)->default('posted');
                $table->decimal('amount', 18, 2);
                $table->string('payment_method', 40)->nullable();
                $table->string('description', 255);
                $table->string('reference_no', 120)->nullable();
                $table->string('source_type', 80)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('source_key', 100)->default('main');
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('voided_at')->nullable();
                $table->text('void_reason')->nullable();
                $table->timestamps();

                $table->unique(['source_type', 'source_id', 'source_key'], 'finance_transactions_source_unique');
                $table->index(['branch_id', 'transaction_date', 'status'], 'finance_transactions_branch_date_index');
                $table->index(['branch_id', 'type', 'status'], 'finance_transactions_branch_type_index');
                $table->index(['payment_method', 'transaction_date'], 'finance_transactions_payment_date_index');
            });
        } elseif (! Schema::hasColumn('finance_transactions', 'payment_method')) {
            Schema::table('finance_transactions', function (Blueprint $table) {
                $table->string('payment_method', 40)->nullable()->after('amount');
                $table->index(['payment_method', 'transaction_date'], 'finance_transactions_payment_date_index');
            });
        }

        if (! Schema::hasColumn('cashier_cash_movements', 'finance_transaction_id')) {
            Schema::table('cashier_cash_movements', function (Blueprint $table) {
                $table->foreignId('finance_transaction_id')
                    ->nullable()
                    ->after('cashier_shift_id')
                    ->constrained('finance_transactions')
                    ->nullOnDelete();
                $table->index(['finance_transaction_id', 'type']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cashier_cash_movements', 'finance_transaction_id')) {
            Schema::table('cashier_cash_movements', function (Blueprint $table) {
                $table->dropIndex(['finance_transaction_id', 'type']);
                $table->dropConstrainedForeignId('finance_transaction_id');
            });
        }

        $hasLegacyFinanceMigration = Schema::hasTable('migrations')
            && DB::table('migrations')
                ->where('migration', '2026_08_31_000001_create_finance_module_tables')
                ->exists();

        // Instalasi lama sudah memiliki jurnal produksi sebelum berkas migrasinya
        // tersedia di repository ini; jangan hapus data tersebut saat rollback.
        if (! $hasLegacyFinanceMigration) {
            Schema::dropIfExists('finance_transactions');
            Schema::dropIfExists('finance_accounts');
            Schema::dropIfExists('finance_categories');
        }
    }
};
