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
        Schema::create('margins', function (Blueprint $table) {
        $table->id();

        // Relasi fleksibel: bisa untuk kategori, golongan, atau obat
        $table->unsignedBigInteger('reference_id')->nullable()
            ->comment('ID referensi tergantung tingkat: kategori, golongan, atau obat');

        // Faktor jual (contoh: 1.25 = margin 25%)
        $table->decimal('faktor_jual', 8, 3)->default(1.000)
            ->comment('Nilai pengali harga beli, contoh: 1.25 = 25% margin');

        // Menentukan tingkat margin
        $table->enum('tingkat', ['kategori', 'golongan', 'main_golongan', 'sub_golongan', 'obat'])
            ->default('kategori')
            ->comment('Menentukan apakah margin berlaku untuk kategori, golongan, atau obat');

        // Status aktif margin
        $table->boolean('is_active')->default(true);

        $table->timestamps();

        // Index untuk pencarian cepat
        $table->index(['tingkat', 'reference_id']);
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('margins');
    }
};
