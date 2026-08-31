<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\StockOpnameDetailModel;
use App\Models\Menu\Stok\StockOpnameModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use App\Services\Menu\Laporan\InventoryReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InventoryReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-28 14:30:00');

        $this->user = User::factory()->create(['name' => 'Apoteker Persediaan']);
        $this->branch = BranchModel::create([
            'code' => 'INV-RPT',
            'name' => 'Cabang Persediaan',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
        $unit = SatuansModel::create(['kode' => 'TAB-INV', 'nama' => 'Tablet', 'is_active' => true]);

        $medicine = MasterObatModel::create([
            'kode_obat' => 'INV-MED-01',
            'nama_obat' => 'Obat Persediaan Utama',
            'satuan_id' => $unit->id,
            'stok_minimum' => 5,
            'is_active' => true,
        ]);
        MasterObatModel::create([
            'kode_obat' => 'INV-EMPTY-01',
            'nama_obat' => 'Obat Kosong',
            'satuan_id' => $unit->id,
            'stok_minimum' => 5,
            'is_active' => true,
        ]);
        $excessMedicine = MasterObatModel::create([
            'kode_obat' => 'INV-EXCESS-01',
            'nama_obat' => 'Obat Berlebih',
            'satuan_id' => $unit->id,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);

        $activeBatch = $this->batch($medicine, 'ACTIVE-01', '2027-08-28', 10, 1000, 2000);
        $this->batch($medicine, 'EXPIRED-01', '2026-08-20', 2, 1000, 2000);
        $this->batch($medicine, 'NEAR-ED-01', '2026-09-15', 3, 1000, 2000);
        $this->batch($excessMedicine, 'EXCESS-01', '2028-08-28', 10, 2000, 4000);

        $this->movement($medicine, $activeBatch, 'masuk', 5, 0, 'Penerimaan barang');
        $this->movement($medicine, $activeBatch, 'penyesuaian_keluar', 0, 1, 'Koreksi obat rusak saat inspeksi');
        $this->movement($medicine, $activeBatch, 'penyesuaian_keluar', 0, 1, 'Barang masuk karantina sementara');

        $opname = StockOpnameModel::create([
            'branch_id' => $this->branch->id,
            'nomor' => 'SO-INV-001',
            'tanggal_opname' => '2026-08-28',
            'status' => StockOpnameModel::STATUS_ADJUSTED,
            'transaction_mode' => StockOpnameModel::MODE_FREEZE,
            'created_by' => $this->user->id,
            'adjusted_by' => $this->user->id,
            'adjusted_at' => now(),
        ]);
        StockOpnameDetailModel::create([
            'stock_opname_id' => $opname->id,
            'stok_batch_id' => $activeBatch->id,
            'obat_id' => $medicine->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'satuan' => 'Tablet',
            'no_batch' => $activeBatch->no_batch,
            'expired_date' => $activeBatch->expired_date,
            'hpp' => 1000,
            'stok_sistem_awal' => 10,
            'stok_sistem_hitung' => 10,
            'stok_fisik' => 8,
            'selisih' => -2,
            'stok_sistem_validasi' => 10,
            'stok_target_validasi' => 8,
            'selisih_validasi' => -2,
            'alasan_selisih' => 'Dua tablet rusak',
            'counted_by' => $this->user->id,
            'counted_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_inventory_workspace_requires_authentication_and_exposes_all_reports(): void
    {
        $this->get(route('laporan.persediaan.index', ['report' => 'posisi-stok']))
            ->assertRedirect(route('login'));

        $response = $this->actingAs($this->user)
            ->get(route('laporan.persediaan.index', ['report' => 'posisi-stok']))
            ->assertOk()
            ->assertSee('Inventory Intelligence')
            ->assertSee('14 perspektif laporan')
            ->assertSee('Ekspor CSV')
            ->assertSee('srColumnToggle', false)
            ->assertSee('srDensityToggle', false)
            ->assertSee('srFocusTable', false)
            ->assertSee('srPageNumbers', false);

        foreach (InventoryReportService::TYPES as $definition) {
            $response->assertSee($definition['short_title']);
        }
    }

    public function test_every_inventory_report_returns_scoped_data_with_complete_columns(): void
    {
        $query = ['date_start' => '2026-08-01', 'date_end' => '2026-12-31'];

        foreach (array_keys(InventoryReportService::TYPES) as $type) {
            $response = $this->actingAs($this->user)
                ->getJson(route('laporan.persediaan.data', ['report' => $type, ...$query]))
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('report.meta.type', $type)
                ->assertJsonStructure(['report' => ['metrics', 'insight', 'chart' => ['labels', 'series'], 'table' => ['columns', 'rows', 'pagination']]]);

            $table = $response->json('report.table');
            $this->assertNotEmpty($table['rows'], "Laporan {$type} seharusnya memiliki data fixture.");

            foreach ($table['columns'] as $column) {
                $this->assertArrayHasKey($column['key'], $table['rows'][0], "Kolom {$column['key']} hilang pada {$type}.");
            }
        }
    }

    public function test_inventory_value_risk_and_audit_reports_calculate_expected_values(): void
    {
        $query = ['date_start' => '2026-08-01', 'date_end' => '2026-12-31'];

        $this->actingAs($this->user)
            ->getJson(route('laporan.persediaan.data', ['report' => 'nilai-persediaan', ...$query]))
            ->assertOk()
            ->assertJsonPath('report.metrics.0.value', 35000)
            ->assertJsonPath('report.metrics.1.value', 70000)
            ->assertJsonPath('report.metrics.2.value', 35000);

        $this->getJson(route('laporan.persediaan.data', ['report' => 'stok-kosong', ...$query]))
            ->assertJsonPath('report.table.rows.0.product_code', 'INV-EMPTY-01');
        $this->getJson(route('laporan.persediaan.data', ['report' => 'stok-berlebih', ...$query]))
            ->assertJsonPath('report.table.rows.0.product_code', 'INV-EXCESS-01')
            ->assertJsonPath('report.table.rows.0.excess_qty', 4);
        $this->getJson(route('laporan.persediaan.data', ['report' => 'expired', ...$query]))
            ->assertJsonPath('report.table.rows.0.batch_number', 'EXPIRED-01')
            ->assertJsonPath('report.table.rows.0.purchase_value', 2000);
        $this->getJson(route('laporan.persediaan.data', ['report' => 'stok-rusak', ...$query]))
            ->assertJsonPath('report.table.rows.0.qty_out', 1);
        $this->getJson(route('laporan.persediaan.data', ['report' => 'stok-karantina', ...$query]))
            ->assertJsonPath('report.table.rows.0.qty_out', 1);
        $this->getJson(route('laporan.persediaan.data', ['report' => 'selisih-opname', ...$query]))
            ->assertJsonPath('report.table.rows.0.difference_qty', -2)
            ->assertJsonPath('report.table.rows.0.difference_value', -2000);
    }

    public function test_inventory_report_rejects_a_branch_outside_user_scope(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'INV-FOREIGN',
            'name' => 'Cabang Asing',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('laporan.persediaan.data', [
                'report' => 'posisi-stok',
                'branch_id' => $foreignBranch->id,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');
    }

    private function batch(MasterObatModel $medicine, string $number, string $expiry, float $qty, float $purchasePrice, float $sellingPrice): StokBatchModel
    {
        return StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => $number,
            'expired_date' => $expiry,
            'qty' => $qty,
            'harga_beli' => $purchasePrice,
            'harga_jual' => $sellingPrice,
            'diskon' => 0,
            'ppn' => 0,
            'last_movement_at' => now(),
            'created_by' => $this->user->id,
        ]);
    }

    private function movement(MasterObatModel $medicine, StokBatchModel $batch, string $type, float $qtyIn, float $qtyOut, string $notes): void
    {
        KartuStokModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'stok_batch_id' => $batch->id,
            'tanggal_mutasi' => now(),
            'jenis_mutasi' => $type,
            'qty_masuk' => $qtyIn,
            'qty_keluar' => $qtyOut,
            'saldo_batch' => $batch->qty,
            'saldo_total' => 15,
            'no_batch' => $batch->no_batch,
            'expired_date' => $batch->expired_date,
            'harga_beli' => $batch->harga_beli,
            'nomor_referensi' => 'ADJ-INV-001',
            'keterangan' => $notes,
            'created_by' => $this->user->id,
        ]);
    }
}
