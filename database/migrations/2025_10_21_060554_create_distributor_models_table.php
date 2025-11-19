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
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();        // contoh: APL, ENS, KFT
            $table->string('nama', 150);                 // contoh: Anugrah Pharmindo Lestari
            $table->string('alamat')->nullable();        // alamat kantor pusat / cabang
            $table->string('telepon', 50)->nullable();   // nomor telepon kantor
            $table->string('email', 100)->nullable();    // email resmi distributor
            $table->boolean('is_active')->default(true); // status aktif/tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_models');
    }
};
