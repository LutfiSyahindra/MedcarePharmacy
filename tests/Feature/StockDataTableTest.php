<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\RiwayatHargaModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockDataTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_history_returns_reasons_as_plain_text_for_client_rendering(): void
    {
        $branch = BranchModel::create(['code' => 'HISTORY', 'name' => 'Cabang Riwayat', 'is_active' => true]);
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'HISTORY-001',
            'nama_obat' => 'ONEMED SYRINGE 50 CC LUER LOCK - LUBANG TENGAH',
        ]);
        $batch = StokBatchModel::create([
            'branch_id' => $branch->id, 'obat_id' => $medicine->id, 'no_batch' => '08112588',
            'qty' => 10, 'harga_beli' => 4000, 'harga_jual' => 5655.19,
        ]);
        $reasons = [
            'Perubahan margin Golongan "ALKES". Harga jual "ONEMED SYRINGE 50 CC LUER LOCK - LUBANG TENGAH" (batch "08112588"): Rp5.880,19 → Rp5.655,19.',
            'Penyesuaian <img src=x onerror="alert(1)"> untuk AT&T; kode literal &quot;.',
        ];
        $expectedReasons = [];

        foreach ($reasons as $reason) {
            $history = RiwayatHargaModel::create([
                'obat_id' => $medicine->id, 'stok_batch_id' => $batch->id,
                'harga_jual_lama' => 5880.19, 'harga_jual_baru' => 5655.19,
                'alasan' => $reason, 'changed_by' => $user->id, 'created_at' => now(),
            ]);
            $expectedReasons[$history->id] = $reason;
        }

        $response = $this->actingAs($user)->getJson(route('stok.riwayatHarga.table', [
            'draw' => 1, 'start' => 0, 'length' => 10, 'stok_batch_id' => $batch->id,
        ]))->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('summary.penurunan', 2);

        foreach ($response->json('data') as $row) {
            $this->assertSame($expectedReasons[$row['id']], $row['alasan']);
        }
    }

    public function test_stock_endpoints_page_in_the_database_and_keep_full_summaries(): void
    {
        $branch = BranchModel::create([
            'code' => 'PERF-01',
            'name' => 'Cabang Performance',
            'is_active' => true,
        ]);
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $user->assignRole(Role::create(['name' => 'Admin', 'guard_name' => 'web']));
        $user->branches()->attach($branch->id);
        $unit = SatuansModel::create([
            'kode' => 'PERF',
            'nama' => 'Unit',
            'is_active' => true,
        ]);

        foreach (range(1, 15) as $number) {
            $medicine = MasterObatModel::create([
                'kode_obat' => 'PERF-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'nama_obat' => 'Obat Performance '.$number,
                'satuan_id' => $unit->id,
                'stok_minimum' => 2,
                'is_active' => true,
            ]);
            $batch = StokBatchModel::create([
                'branch_id' => $branch->id,
                'obat_id' => $medicine->id,
                'no_batch' => 'BATCH-'.$number,
                'expired_date' => today()->addYear()->toDateString(),
                'qty' => 10,
                'harga_beli' => 1000,
                'biaya_lain' => 100,
                'harga_jual' => 1500,
                'diskon' => 0,
                'ppn' => 0,
                'created_by' => $user->id,
            ]);
            KartuStokModel::create([
                'branch_id' => $branch->id,
                'obat_id' => $medicine->id,
                'stok_batch_id' => $batch->id,
                'tanggal_mutasi' => now()->addSeconds($number),
                'jenis_mutasi' => 'masuk',
                'qty_masuk' => 10,
                'saldo_batch' => 10,
                'saldo_total' => 10,
                'no_batch' => $batch->no_batch,
                'harga_beli' => 1000,
                'created_by' => $user->id,
            ]);
        }

        $dataTable = ['draw' => 1, 'start' => 0, 'length' => 5];

        $stock = $this->actingAs($user)->getJson(route('stok.table', $dataTable));
        $stock->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('recordsTotal', 15)
            ->assertJsonPath('recordsFiltered', 15)
            ->assertJsonPath('summary.total_item', 15)
            ->assertJsonPath('summary.total_stok', 150)
            ->assertJsonPath('summary.nilai_stok', 150000)
            ->assertJsonPath('summary.nilai_stok_jual', 225000)
            ->assertJsonPath('data.0.nilai_stok', 10000)
            ->assertJsonPath('data.0.nilai_stok_jual', 15000);

        $batches = $this->getJson(route('stok.batchTable', $dataTable));
        $batches->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('recordsTotal', 15)
            ->assertJsonPath('summary.total_batch', 15)
            ->assertJsonPath('summary.total_stok', 150)
            ->assertJsonPath('summary.nilai_stok', 150000)
            ->assertJsonPath('summary.total_biaya_lain', 15000)
            ->assertJsonPath('summary.nilai_stok_jual', 225000)
            ->assertJsonPath('data.0.diskon_persen', 0)
            ->assertJsonPath('data.0.harga_jual_sebelum_diskon', 1500)
            ->assertJsonPath('data.0.harga_jual_sesudah_diskon', 1500)
            ->assertJsonPath('data.0.nilai_stok', 10000)
            ->assertJsonPath('data.0.nilai_biaya_lain', 1000)
            ->assertJsonPath('data.0.nilai_stok_jual', 15000);

        $cards = $this->getJson(route('kartuStok.table', $dataTable));
        $cards->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('recordsTotal', 15)
            ->assertJsonPath('summary.jumlah_mutasi', 15)
            ->assertJsonPath('summary.total_masuk', 150);
    }
}
