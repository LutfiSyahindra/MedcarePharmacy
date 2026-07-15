<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('master_obats', 'harga_jual')) {
            return;
        }

        if (Schema::hasColumn('stok_batches', 'harga_jual')) {
            DB::table('stok_batches')
                ->select('id', 'obat_id')
                ->where(function ($query) {
                    $query->whereNull('harga_jual')
                        ->orWhere('harga_jual', '<=', 0);
                })
                ->chunkById(200, function ($batches) {
                    $hargaJualByObat = DB::table('master_obats')
                        ->whereIn('id', $batches->pluck('obat_id')->unique()->values())
                        ->pluck('harga_jual', 'id');

                    foreach ($batches as $batch) {
                        $hargaJual = (float) ($hargaJualByObat[$batch->obat_id] ?? 0);

                        if ($hargaJual <= 0) {
                            continue;
                        }

                        DB::table('stok_batches')
                            ->where('id', $batch->id)
                            ->update(['harga_jual' => $hargaJual]);
                    }
                });
        }

        Schema::table('master_obats', function (Blueprint $table) {
            $table->dropColumn('harga_jual');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('master_obats', 'harga_jual')) {
            Schema::table('master_obats', function (Blueprint $table) {
                $table->decimal('harga_jual', 15, 2)->default(0)->after('harga_beli');
            });
        }
    }
};
