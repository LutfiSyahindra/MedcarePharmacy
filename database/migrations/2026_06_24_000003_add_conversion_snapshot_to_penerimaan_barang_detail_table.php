<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_barang_detail', function (Blueprint $table) {
            if (! Schema::hasColumn('penerimaan_barang_detail', 'qty_diterima_stok')) {
                $table->decimal('qty_diterima_stok', 15, 2)->default(0)->after('qty_diterima');
            }

            if (! Schema::hasColumn('penerimaan_barang_detail', 'konversi_satuan')) {
                $table->decimal('konversi_satuan', 15, 4)->default(1)->after('qty_diterima_stok');
            }

            if (! Schema::hasColumn('penerimaan_barang_detail', 'satuan_beli')) {
                $table->string('satuan_beli', 50)->nullable()->after('konversi_satuan');
            }

            if (! Schema::hasColumn('penerimaan_barang_detail', 'satuan_stok')) {
                $table->string('satuan_stok', 50)->nullable()->after('satuan_beli');
            }

            if (! Schema::hasColumn('penerimaan_barang_detail', 'harga_beli_stok')) {
                $table->decimal('harga_beli_stok', 15, 2)->default(0)->after('harga_beli');
            }
        });

        $details = DB::table('penerimaan_barang_detail as detail')
            ->leftJoin('purchase_order_details as po_detail', 'po_detail.id', '=', 'detail.purchase_order_detail_id')
            ->leftJoin('obat_satuan_conversions as konversi', 'konversi.id', '=', 'po_detail.satuan_konversi')
            ->leftJoin('master_obats as obat', 'obat.id', '=', 'detail.obat_id')
            ->leftJoin('satuans as satuan_stok', 'satuan_stok.id', '=', 'obat.satuan_id')
            ->select([
                'detail.id',
                'detail.qty_diterima',
                'detail.harga_beli',
                'konversi.konversi as faktor',
                'satuan_stok.nama as satuan_stok',
            ])
            ->get();

        foreach ($details as $detail) {
            $faktor = max(1, (float) ($detail->faktor ?: 1));
            DB::table('penerimaan_barang_detail')->where('id', $detail->id)->update([
                'qty_diterima_stok' => (float) $detail->qty_diterima * $faktor,
                'konversi_satuan' => $faktor,
                'satuan_stok' => $detail->satuan_stok,
                'harga_beli_stok' => $faktor > 0 ? (float) $detail->harga_beli / $faktor : (float) $detail->harga_beli,
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('penerimaan_barang_detail', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaan_barang_detail', 'harga_beli_stok')) {
                $table->dropColumn('harga_beli_stok');
            }

            if (Schema::hasColumn('penerimaan_barang_detail', 'satuan_stok')) {
                $table->dropColumn('satuan_stok');
            }

            if (Schema::hasColumn('penerimaan_barang_detail', 'satuan_beli')) {
                $table->dropColumn('satuan_beli');
            }

            if (Schema::hasColumn('penerimaan_barang_detail', 'konversi_satuan')) {
                $table->dropColumn('konversi_satuan');
            }

            if (Schema::hasColumn('penerimaan_barang_detail', 'qty_diterima_stok')) {
                $table->dropColumn('qty_diterima_stok');
            }
        });
    }
};
