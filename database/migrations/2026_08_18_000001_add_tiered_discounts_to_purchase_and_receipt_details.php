<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->decimal('diskon_1', 8, 2)->default(0)->after('harga_estimasi');
            $table->decimal('diskon_2', 8, 2)->default(0)->after('diskon_1');
            $table->decimal('diskon_3', 8, 2)->default(0)->after('diskon_2');
        });

        Schema::table('penerimaan_barang_detail', function (Blueprint $table) {
            $table->decimal('diskon_1', 8, 2)->default(0)->after('harga_beli');
            $table->decimal('diskon_2', 8, 2)->default(0)->after('diskon_1');
            $table->decimal('diskon_3', 8, 2)->default(0)->after('diskon_2');
        });

        Schema::table('retur_pembelian_detail', function (Blueprint $table) {
            $table->decimal('diskon_1', 8, 2)->default(0)->after('harga_beli');
            $table->decimal('diskon_2', 8, 2)->default(0)->after('diskon_1');
            $table->decimal('diskon_3', 8, 2)->default(0)->after('diskon_2');
        });

        DB::table('penerimaan_barang_detail')->update([
            'diskon_1' => DB::raw('diskon'),
        ]);

        DB::table('retur_pembelian_detail')->update([
            'diskon_1' => DB::raw('diskon'),
        ]);

        DB::table('retur_pembelian_detail')
            ->select(['id', 'penerimaan_barang_detail_id'])
            ->whereNotNull('penerimaan_barang_detail_id')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                $receiptDiscounts = DB::table('penerimaan_barang_detail')
                    ->whereIn('id', $rows->pluck('penerimaan_barang_detail_id')->all())
                    ->get(['id', 'diskon_1', 'diskon_2', 'diskon_3'])
                    ->keyBy('id');

                foreach ($rows as $row) {
                    $receiptDiscount = $receiptDiscounts->get($row->penerimaan_barang_detail_id);

                    if (! $receiptDiscount) {
                        continue;
                    }

                    DB::table('retur_pembelian_detail')
                        ->where('id', $row->id)
                        ->update([
                            'diskon_1' => $receiptDiscount->diskon_1,
                            'diskon_2' => $receiptDiscount->diskon_2,
                            'diskon_3' => $receiptDiscount->diskon_3,
                        ]);
                }
            });

        $legacyDiscounts = [];

        DB::table('penerimaan_barang_detail')
            ->select(['id', 'purchase_order_detail_id', 'diskon'])
            ->whereNotNull('purchase_order_detail_id')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$legacyDiscounts) {
                foreach ($rows as $row) {
                    $legacyDiscounts[(int) $row->purchase_order_detail_id] = (float) $row->diskon;
                }
            });

        DB::table('purchase_order_details')
            ->select(['id', 'qty', 'harga_estimasi'])
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($legacyDiscounts) {
                foreach ($rows as $row) {
                    $discount = round(min(100, max(0, $legacyDiscounts[(int) $row->id] ?? 0)), 2);
                    $grossAmount = (float) $row->qty * (float) $row->harga_estimasi;
                    $subtotal = round($grossAmount * (1 - ($discount / 100)), 2);

                    DB::table('purchase_order_details')
                        ->where('id', $row->id)
                        ->update([
                            'diskon_1' => $discount,
                            'subtotal' => $subtotal,
                        ]);
                }
            });

        DB::table('purchase_orders')
            ->select('id')
            ->orderBy('id')
            ->chunk(500, function ($orders) {
                foreach ($orders as $order) {
                    DB::table('purchase_orders')
                        ->where('id', $order->id)
                        ->update([
                            'total_estimasi' => DB::table('purchase_order_details')
                                ->where('purchase_order_id', $order->id)
                                ->sum('subtotal'),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('retur_pembelian_detail', function (Blueprint $table) {
            $table->dropColumn(['diskon_1', 'diskon_2', 'diskon_3']);
        });

        Schema::table('penerimaan_barang_detail', function (Blueprint $table) {
            $table->dropColumn(['diskon_1', 'diskon_2', 'diskon_3']);
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn(['diskon_1', 'diskon_2', 'diskon_3']);
        });
    }
};
