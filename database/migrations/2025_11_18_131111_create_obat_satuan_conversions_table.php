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
         Schema::create('obat_satuan_conversions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('obat_id');

            // Nama satuan pembelian (box, strip, pcs, botol, pack)
            $table->unsignedBigInteger('satuan');

            // Berapa PCS dalam 1 satuan ini
            $table->integer('konversi')->default(1);

            // 1: default (dipakai otomatis di PO)
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->foreign('obat_id')
                ->references('id')
                ->on('master_obats')
                ->onDelete('cascade');
            $table->foreign('satuan')
                ->references('id')
                ->on('satuans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat_satuan_conversions');
    }
};
