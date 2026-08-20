<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('penerimaan_barang')
            ->where('supplier_compensation_discount', '>', 0)
            ->update([
                'total_faktur' => DB::raw('COALESCE(total_faktur, 0) + COALESCE(supplier_compensation_discount, 0)'),
                'grand_total' => DB::raw('COALESCE(grand_total, 0) + COALESCE(supplier_compensation_discount, 0)'),
            ]);
    }

    public function down(): void
    {
        DB::table('penerimaan_barang')
            ->where('supplier_compensation_discount', '>', 0)
            ->update([
                'total_faktur' => DB::raw('COALESCE(total_faktur, 0) - COALESCE(supplier_compensation_discount, 0)'),
                'grand_total' => DB::raw('COALESCE(grand_total, 0) - COALESCE(supplier_compensation_discount, 0)'),
            ]);
    }
};
