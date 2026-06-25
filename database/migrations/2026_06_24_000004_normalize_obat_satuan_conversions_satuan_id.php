<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('obat_satuan_conversions', 'satuan_id')) {
            return;
        }

        Schema::table('obat_satuan_conversions', function (Blueprint $table) {
            $table->unsignedBigInteger('satuan_id')->nullable()->after('obat_id');
        });

        if (Schema::hasColumn('obat_satuan_conversions', 'satuan')) {
            DB::table('obat_satuan_conversions')->update([
                'satuan_id' => DB::raw('satuan'),
            ]);
        }

        Schema::table('obat_satuan_conversions', function (Blueprint $table) {
            $table->foreign('satuan_id')
                ->references('id')
                ->on('satuans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        //
    }
};
