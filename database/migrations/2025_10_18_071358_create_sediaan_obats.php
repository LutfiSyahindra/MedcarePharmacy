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
        Schema::create('sediaan_obats', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();     // contoh: TBL, KPS, SRP
            $table->string('nama', 100);              // contoh: Tablet, Kapsul, Sirup
            $table->text('keterangan')->nullable();   // deskripsi tambahan (opsional)
            $table->boolean('is_active')->default(true); // status aktif/tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sediaan_obats');
    }
};
