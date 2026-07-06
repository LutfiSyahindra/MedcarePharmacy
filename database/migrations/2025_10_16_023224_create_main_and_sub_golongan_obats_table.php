<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('main_golongan_obats')) {
            Schema::create('main_golongan_obats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('golongan_id')->constrained('golongan_obats')->cascadeOnDelete();
                $table->string('kode', 20)->unique();
                $table->string('nama', 100);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sub_golongan_obats')) {
            Schema::create('sub_golongan_obats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('main_golongan_id')->constrained('main_golongan_obats')->cascadeOnDelete();
                $table->string('kode', 20)->unique();
                $table->string('nama', 100);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_golongan_obats');
        Schema::dropIfExists('main_golongan_obats');
    }
};
