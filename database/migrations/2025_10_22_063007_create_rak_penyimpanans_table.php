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
        Schema::create('rak_peyimpanans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();        // contoh: RKA1, LMRP, GDG2
            $table->string('nama', 100);                 // contoh: Rak A1, Lemari Pendingin, Gudang 2
            $table->string('lokasi')->nullable();        // lokasi fisik, misal: "Depan", "Belakang", "Lantai 2"
            $table->text('keterangan')->nullable();      // deskripsi tambahan (misal: "Untuk obat suhu ruang")
            $table->boolean('is_active')->default(true); // status aktif/tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rak_penyimpanans');
    }
};
