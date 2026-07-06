<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_obats')) {
            if (!Schema::hasColumn('master_obats', 'main_golongan_id')) {
                Schema::table('master_obats', function (Blueprint $table) {
                    $table->foreignId('main_golongan_id')
                        ->nullable()
                        ->after('golongan_id')
                        ->constrained('main_golongan_obats')
                        ->nullOnDelete();
                });
            }

            if (!Schema::hasColumn('master_obats', 'sub_golongan_id')) {
                Schema::table('master_obats', function (Blueprint $table) {
                    $table->foreignId('sub_golongan_id')
                        ->nullable()
                        ->after('main_golongan_id')
                        ->constrained('sub_golongan_obats')
                        ->nullOnDelete();
                });
            }

            if (Schema::hasColumn('master_obats', 'sub_kategori_id')) {
                Schema::table('master_obats', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['sub_kategori_id']);
                    } catch (\Throwable $e) {
                        //
                    }

                    $table->dropColumn('sub_kategori_id');
                });
            }

            if (Schema::hasColumn('master_obats', 'main_category_id')) {
                Schema::table('master_obats', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['main_category_id']);
                    } catch (\Throwable $e) {
                        //
                    }

                    $table->dropColumn('main_category_id');
                });
            }
        }

        if (Schema::hasTable('margins')) {
            DB::table('margins')
                ->whereIn('tingkat', ['kategoriUtama', 'sub_kategori'])
                ->update(['tingkat' => 'kategori']);

            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement(
                    "ALTER TABLE margins MODIFY tingkat ENUM('kategori','golongan','main_golongan','sub_golongan','obat') DEFAULT 'kategori' COMMENT 'Menentukan apakah margin berlaku untuk kategori, golongan, atau obat'"
                );
            }
        }

        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('main_category');
    }

    public function down(): void
    {
        if (Schema::hasTable('master_obats')) {
            if (Schema::hasColumn('master_obats', 'sub_golongan_id')) {
                Schema::table('master_obats', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['sub_golongan_id']);
                    } catch (\Throwable $e) {
                        //
                    }

                    $table->dropColumn('sub_golongan_id');
                });
            }

            if (Schema::hasColumn('master_obats', 'main_golongan_id')) {
                Schema::table('master_obats', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['main_golongan_id']);
                    } catch (\Throwable $e) {
                        //
                    }

                    $table->dropColumn('main_golongan_id');
                });
            }
        }
    }
};
