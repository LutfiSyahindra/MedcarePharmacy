<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->string('diskon_untuk', 20)
                ->nullable()
                ->after('total_diskon');
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->dropColumn('diskon_untuk');
        });
    }
};
