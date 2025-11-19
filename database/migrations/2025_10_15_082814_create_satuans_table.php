<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('satuans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique(); // contoh: 'TAB', 'BOT', 'STR'
            $table->string('nama', 100);          // contoh: 'Tablet', 'Botol', 'Strip'
            $table->string('keterangan')->nullable(); // deskripsi tambahan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satuans');
    }
};
