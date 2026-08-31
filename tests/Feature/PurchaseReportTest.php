<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use App\Models\User;
use App\Services\Menu\Laporan\PurchaseReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private DistributorModel $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-28 14:30:00');

        $this->user = User::factory()->create(['name' => 'Apoteker Pembelian']);
        $this->branch = BranchModel::create([
            'code' => 'BUY-RPT',
            'name' => 'Cabang Pembelian',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
        $this->supplier = DistributorModel::create([
            'kode' => 'SUP-RPT',
            'nama' => 'Supplier Andal',
            'is_active' => true,
        ]);
        $unit = SatuansModel::create(['kode' => 'TAB', 'nama' => 'Tablet', 'is_active' => true]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'BUY-MED-01',
            'nama_obat' => 'Obat Laporan Pembelian',
            'satuan_id' => $unit->id,
            'is_active' => true,
        ]);

        $poId = DB::table('purchase_orders')->insertGetId([
            'no_po' => 'PO-RPT-001',
            'distributor_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'tanggal_po' => '2026-08-20',
            'total_estimasi' => 100000,
            'status' => 'approved',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $poDetailId = DB::table('purchase_order_details')->insertGetId([
            'purchase_order_id' => $poId,
            'obat_id' => $medicine->id,
            'qty' => 10,
            'harga_estimasi' => 10000,
            'diskon_1' => 0,
            'diskon_2' => 0,
            'diskon_3' => 0,
            'subtotal' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $receiptId = DB::table('penerimaan_barang')->insertGetId([
            'nomor_penerimaan' => 'RCV-RPT-001',
            'purchase_order_id' => $poId,
            'distributor_id' => $this->supplier->id,
            'nomor_faktur' => 'INV-RPT-001',
            'nomor_surat_jalan' => 'SJ-RPT-001',
            'tanggal_penerimaan' => '2026-08-24',
            'tanggal_faktur' => '2026-08-24',
            'tanggal_jatuh_tempo' => '2026-08-31',
            'total_barang' => 1,
            'total_qty' => 8,
            'subtotal' => 100000,
            'total_diskon' => 0,
            'total_ppn' => 10000,
            'grand_total' => 110000,
            'diskon' => 0,
            'pajak' => 10000,
            'biaya_lain' => 0,
            'total_faktur' => 110000,
            'status_pembayaran' => 'sebagian',
            'jumlah_dibayar' => 40000,
            'sisa_hutang' => 70000,
            'supplier_compensation_discount' => 0,
            'status' => 'posted',
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => '2026-08-24 10:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $receiptDetailId = DB::table('penerimaan_barang_detail')->insertGetId([
            'penerimaan_barang_id' => $receiptId,
            'purchase_order_detail_id' => $poDetailId,
            'obat_id' => $medicine->id,
            'qty_po' => 10,
            'qty_diterima' => 8,
            'qty_diterima_stok' => 80,
            'konversi_satuan' => 10,
            'satuan_beli' => 'Strip',
            'satuan_stok' => 'Tablet',
            'no_batch' => 'BATCH-BUY-01',
            'expired_date' => '2028-08-31',
            'harga_beli' => 10000,
            'harga_beli_stok' => 1000,
            'diskon_1' => 0,
            'diskon_2' => 0,
            'diskon_3' => 0,
            'diskon' => 0,
            'ppn' => 10,
            'subtotal' => 80000,
            'nilai_diskon' => 0,
            'nilai_ppn' => 8000,
            'total' => 88000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $returnId = DB::table('retur_pembelian')->insertGetId([
            'nomor_retur' => 'RET-BUY-001',
            'penerimaan_barang_id' => $receiptId,
            'purchase_order_id' => $poId,
            'distributor_id' => $this->supplier->id,
            'tanggal_retur' => '2026-08-26',
            'total_barang' => 1,
            'total_qty' => 1,
            'subtotal' => 10000,
            'total_diskon' => 0,
            'total_ppn' => 1000,
            'grand_total' => 11000,
            'expects_compensation' => true,
            'compensation_due_date' => '2026-09-05',
            'status' => 'posted',
            'alasan' => 'Kemasan rusak',
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => '2026-08-26 11:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('retur_pembelian_detail')->insert([
            'retur_pembelian_id' => $returnId,
            'penerimaan_barang_detail_id' => $receiptDetailId,
            'purchase_order_detail_id' => $poDetailId,
            'obat_id' => $medicine->id,
            'qty_diterima' => 8,
            'qty_retur' => 1,
            'qty_retur_stok' => 10,
            'konversi_satuan' => 10,
            'satuan_beli' => 'Strip',
            'satuan_stok' => 'Tablet',
            'no_batch' => 'BATCH-BUY-01',
            'harga_beli' => 10000,
            'harga_beli_stok' => 1000,
            'diskon_1' => 0,
            'diskon_2' => 0,
            'diskon_3' => 0,
            'diskon' => 0,
            'ppn' => 10,
            'subtotal' => 10000,
            'nilai_diskon' => 0,
            'nilai_ppn' => 1000,
            'total' => 11000,
            'alasan_item' => 'Kemasan rusak',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_purchase_report_workspace_exposes_all_views_and_supplier_filter(): void
    {
        $this->get(route('laporan.pembelian.index', ['report' => 'pembelian']))
            ->assertRedirect(route('login'));

        $response = $this->actingAs($this->user)
            ->get(route('laporan.pembelian.index', ['report' => 'pembelian']));

        $response->assertOk()
            ->assertSee('Procurement Intelligence')
            ->assertSee('srSupplier', false)
            ->assertSee('Ekspor CSV')
            ->assertSee('srColumnToggle', false)
            ->assertSee('srDensityToggle', false)
            ->assertSee('srFocusTable', false)
            ->assertSee('srPageNumbers', false);

        foreach (PurchaseReportService::TYPES as $definition) {
            $response->assertSee($definition['short_title']);
        }
    }

    public function test_every_purchase_report_returns_scoped_operational_data(): void
    {
        $query = ['date_start' => '2026-08-01', 'date_end' => '2026-09-30'];

        foreach (array_keys(PurchaseReportService::TYPES) as $type) {
            $response = $this->actingAs($this->user)
                ->getJson(route('laporan.pembelian.data', ['report' => $type, ...$query]))
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('report.meta.type', $type)
                ->assertJsonStructure(['report' => ['metrics', 'chart' => ['labels', 'series'], 'table' => ['columns', 'rows', 'pagination']]]);

            $table = $response->json('report.table');
            $this->assertNotEmpty($table['rows'], "Laporan {$type} seharusnya memiliki data fixture.");

            foreach ($table['columns'] as $column) {
                $this->assertArrayHasKey($column['key'], $table['rows'][0]);
            }
        }

        $this->getJson(route('laporan.pembelian.data', ['report' => 'pembelian', ...$query]))
            ->assertJsonPath('report.table.rows.0.no_po', 'PO-RPT-001')
            ->assertJsonPath('report.table.rows.0.actual_value', 110000);
        $this->getJson(route('laporan.pembelian.data', ['report' => 'supplier', ...$query]))
            ->assertJsonPath('report.table.rows.0.supplier_name', 'Supplier Andal')
            ->assertJsonPath('report.table.rows.0.fulfillment_rate', 80);
        $this->getJson(route('laporan.pembelian.data', ['report' => 'belum-lunas', ...$query]))
            ->assertJsonPath('report.table.rows.0.invoice_number', 'INV-RPT-001')
            ->assertJsonPath('report.table.rows.0.outstanding_value', 70000);
        $this->getJson(route('laporan.pembelian.data', [
            'report' => 'penerimaan',
            'supplier_id' => $this->supplier->id,
            ...$query,
        ]))->assertJsonPath('report.meta.supplier_label', 'Supplier Andal');
    }

    public function test_purchase_report_rejects_unavailable_branch_and_invalid_period(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'BUY-FOREIGN',
            'name' => 'Cabang Asing',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('laporan.pembelian.data', [
                'report' => 'pembelian',
                'branch_id' => $foreignBranch->id,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');

        $this->getJson(route('laporan.pembelian.data', [
            'report' => 'pembelian',
            'date_start' => '2026-08-28',
            'date_end' => '2026-08-01',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date_end');
    }
}
