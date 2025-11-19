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
        Schema::create('golongan_obats', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique(); // contoh: ANT, VIT, ALG
            $table->string('nama', 100);          // contoh: Antibiotik, Vitamin, Analgesik
            $table->text('keterangan')->nullable(); // deskripsi tambahan golongan
            $table->boolean('is_active')->default(true); // status aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('golongan_obats');
    }
};
