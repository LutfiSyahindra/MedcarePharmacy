<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->decimal('ppn', 8, 2)
                ->nullable()
                ->default(11)
                ->after('diskon_3');
        });

        // PO lama tidak pernah menyimpan PPN. Pertahankan subtotal historisnya,
        // sedangkan detail PO baru memakai default database 11%.
        DB::table('purchase_order_details')->update(['ppn' => null]);
    }

    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('ppn');
        });
    }
};
