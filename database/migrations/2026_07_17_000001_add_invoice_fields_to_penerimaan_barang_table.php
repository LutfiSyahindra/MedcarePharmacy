<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penerimaan_barang')) {
            return;
        }

        Schema::table('penerimaan_barang', function (Blueprint $table) {
            if (! Schema::hasColumn('penerimaan_barang', 'tanggal_faktur')) {
                $table->date('tanggal_faktur')->nullable();
            }

            if (! Schema::hasColumn('penerimaan_barang', 'tanggal_jatuh_tempo')) {
                $table->date('tanggal_jatuh_tempo')->nullable();
            }

            if (! Schema::hasColumn('penerimaan_barang', 'diskon')) {
                $table->decimal('diskon', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('penerimaan_barang', 'pajak')) {
                $table->decimal('pajak', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('penerimaan_barang', 'biaya_lain')) {
                $table->decimal('biaya_lain', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('penerimaan_barang', 'total_faktur')) {
                $table->decimal('total_faktur', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('penerimaan_barang', 'status_pembayaran')) {
                $table->string('status_pembayaran', 30)->default('belum_dibayar');
            }

            if (! Schema::hasColumn('penerimaan_barang', 'jumlah_dibayar')) {
                $table->decimal('jumlah_dibayar', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('penerimaan_barang', 'sisa_hutang')) {
                $table->decimal('sisa_hutang', 15, 2)->default(0);
            }
        });

        DB::table('penerimaan_barang')
            ->where(function ($query) {
                $query->whereNull('total_faktur')
                    ->orWhere('total_faktur', 0);
            })
            ->update([
                'diskon' => DB::raw('total_diskon'),
                'pajak' => DB::raw('total_ppn'),
                'total_faktur' => DB::raw('grand_total'),
                'sisa_hutang' => DB::raw('CASE WHEN grand_total - COALESCE(jumlah_dibayar, 0) > 0 THEN grand_total - COALESCE(jumlah_dibayar, 0) ELSE 0 END'),
                'status_pembayaran' => DB::raw("CASE WHEN COALESCE(jumlah_dibayar, 0) <= 0 THEN 'belum_dibayar' WHEN COALESCE(jumlah_dibayar, 0) >= grand_total THEN 'lunas' ELSE 'sebagian' END"),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('penerimaan_barang')) {
            return;
        }

        Schema::table('penerimaan_barang', function (Blueprint $table) {
            foreach ([
                'tanggal_faktur',
                'tanggal_jatuh_tempo',
                'diskon',
                'pajak',
                'biaya_lain',
                'total_faktur',
                'status_pembayaran',
                'jumlah_dibayar',
                'sisa_hutang',
            ] as $column) {
                if (Schema::hasColumn('penerimaan_barang', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
