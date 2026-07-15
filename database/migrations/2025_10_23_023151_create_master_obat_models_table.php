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
        Schema::create('master_obats', function (Blueprint $table) {
            $table->id();
            $table->string('kode_obat', 50)->unique();           // contoh: OBT0001, PAR500, AMOX250
            $table->string('nama_obat', 150);                    // contoh: Paracetamol 500mg

            // Relasi kategori dan golongan
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('golongan_id')->nullable()->constrained('golongan_obats')->nullOnDelete();
            $table->foreignId('main_golongan_id')->nullable()->constrained('main_golongan_obats')->nullOnDelete();
            $table->foreignId('sub_golongan_id')->nullable()->constrained('sub_golongan_obats')->nullOnDelete();

            // Relasi ke tabel master lain
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->nullOnDelete();
            $table->foreignId('sediaan_id')->nullable()->constrained('sediaan_obats')->nullOnDelete();
            $table->foreignId('pabrikan_id')->nullable()->constrained('pabrikan')->nullOnDelete();
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->nullOnDelete();
            $table->foreignId('rak_id')->nullable()->constrained('rak_penyimpanans')->nullOnDelete();

            // Informasi tambahan obat
            $table->string('komposisi')->nullable();
            $table->string('indikasi')->nullable();
            $table->string('dosis')->nullable();
            $table->string('kemasan')->nullable();

            // Ambang stok & harga beli
            $table->integer('stok_minimum')->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);

            // Informasi batch & kedaluwarsa
            $table->date('tgl_kadaluarsa')->nullable();
            $table->string('no_batch', 50)->nullable();

            // Jenis & status
            $table->boolean('is_generik')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_obats');
    }
};
