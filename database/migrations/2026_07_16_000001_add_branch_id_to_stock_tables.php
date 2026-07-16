<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'stok_batches_obat_batch_expired_diskon_ppn_unique';

    private const NEW_UNIQUE = 'stok_batches_branch_obat_batch_expired_diskon_ppn_unique';

    private const RECEIPT_REFERENCE = 'App\\Models\\Menu\\PembelianPenerimaan\\PenerimaanBarangModel';

    public function up(): void
    {
        if (! Schema::hasColumn('stok_batches', 'branch_id')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('kartu_stok', 'branch_id')) {
            Schema::table('kartu_stok', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        $this->backfillBranchIds();
        $this->dropUniqueSafely(self::OLD_UNIQUE);

        Schema::table('stok_batches', function (Blueprint $table) {
            $table->unique(['branch_id', 'obat_id', 'no_batch', 'expired_date', 'diskon', 'ppn'], self::NEW_UNIQUE);
            $table->index(['branch_id', 'obat_id', 'expired_date'], 'stok_batches_branch_obat_expired_index');
        });

        Schema::table('kartu_stok', function (Blueprint $table) {
            $table->index(['branch_id', 'obat_id', 'tanggal_mutasi'], 'kartu_stok_branch_obat_tanggal_index');
            $table->index(['branch_id', 'stok_batch_id', 'tanggal_mutasi'], 'kartu_stok_branch_batch_tanggal_index');
        });
    }

    public function down(): void
    {
        $this->dropIndexSafely('kartu_stok', 'kartu_stok_branch_batch_tanggal_index');
        $this->dropIndexSafely('kartu_stok', 'kartu_stok_branch_obat_tanggal_index');
        $this->dropIndexSafely('stok_batches', 'stok_batches_branch_obat_expired_index');
        $this->dropUniqueSafely(self::NEW_UNIQUE);

        if (Schema::hasColumn('kartu_stok', 'branch_id')) {
            Schema::table('kartu_stok', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }

        if (Schema::hasColumn('stok_batches', 'branch_id')) {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }

        try {
            Schema::table('stok_batches', function (Blueprint $table) {
                $table->unique(['obat_id', 'no_batch', 'expired_date', 'diskon', 'ppn'], self::OLD_UNIQUE);
            });
        } catch (\Throwable) {
            // Data split per branch can make the old global unique index impossible to restore.
        }
    }

    private function backfillBranchIds(): void
    {
        if (Schema::hasTable('penerimaan_barang_detail') && Schema::hasColumn('penerimaan_barang_detail', 'stok_batch_id')) {
            DB::table('penerimaan_barang_detail as detail')
                ->join('penerimaan_barang as header', 'header.id', '=', 'detail.penerimaan_barang_id')
                ->join('purchase_orders as po', 'po.id', '=', 'header.purchase_order_id')
                ->whereNotNull('detail.stok_batch_id')
                ->whereNotNull('po.branch_id')
                ->select('detail.stok_batch_id', DB::raw('MIN(po.branch_id) as branch_id'))
                ->groupBy('detail.stok_batch_id')
                ->orderBy('detail.stok_batch_id')
                ->chunk(200, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('stok_batches')
                            ->where('id', $row->stok_batch_id)
                            ->whereNull('branch_id')
                            ->update(['branch_id' => $row->branch_id]);
                    }
                });
        }

        DB::table('kartu_stok')
            ->whereNotNull('stok_batch_id')
            ->whereNull('branch_id')
            ->select('id', 'stok_batch_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                $batchBranches = DB::table('stok_batches')
                    ->whereIn('id', $rows->pluck('stok_batch_id')->filter()->all())
                    ->pluck('branch_id', 'id');

                foreach ($rows as $row) {
                    $branchId = $batchBranches[$row->stok_batch_id] ?? null;

                    if ($branchId) {
                        DB::table('kartu_stok')
                            ->where('id', $row->id)
                            ->update(['branch_id' => $branchId]);
                    }
                }
            });

        DB::table('kartu_stok')
            ->whereNull('branch_id')
            ->where('reference_type', self::RECEIPT_REFERENCE)
            ->whereNotNull('reference_id')
            ->select('id', 'reference_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                $receiptBranches = DB::table('penerimaan_barang as header')
                    ->join('purchase_orders as po', 'po.id', '=', 'header.purchase_order_id')
                    ->whereIn('header.id', $rows->pluck('reference_id')->filter()->all())
                    ->select('header.id as receipt_id', 'po.branch_id as branch_id')
                    ->pluck('branch_id', 'receipt_id');

                foreach ($rows as $row) {
                    $branchId = $receiptBranches[$row->reference_id] ?? null;

                    if ($branchId) {
                        DB::table('kartu_stok')
                            ->where('id', $row->id)
                            ->update(['branch_id' => $branchId]);
                    }
                }
            });
    }

    private function dropUniqueSafely(string $indexName): void
    {
        try {
            Schema::table('stok_batches', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Throwable) {
            // The index may not exist in databases that were migrated from a different state.
        }
    }

    private function dropIndexSafely(string $tableName, string $indexName): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } catch (\Throwable) {
            // The index may not exist in databases that were migrated from a different state.
        }
    }
};
