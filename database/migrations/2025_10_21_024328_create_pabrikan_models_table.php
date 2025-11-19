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
        Schema::create('pabrikan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();       // contoh: KLF, SAN, DEX
            $table->string('nama', 150);                // contoh: Kalbe Farma, Sanbe Farma, Dexa Medica
            $table->string('alamat')->nullable();       // alamat kantor pusat
            $table->string('telepon', 50)->nullable();  // nomor telepon pabrikan
            $table->string('email', 100)->nullable();   // email resmi
            $table->boolean('is_active')->default(true); // status aktif/tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pabrikan_models');
    }
};
