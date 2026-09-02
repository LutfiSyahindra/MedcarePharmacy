<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Penjualan\PenjualanTransactionBatchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Penjualan\ReturPenjualanBatchModel;
use App\Models\Menu\Penjualan\ReturPenjualanDetailModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProfitabilityAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private MasterObatModel $productA;

    private MasterObatModel $productB;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-31 16:00:00');

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'PROFIT-01',
            'name' => 'Cabang Profit',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
        $this->productA = MasterObatModel::create([
            'kode_obat' => 'PROFIT-A',
            'nama_obat' => 'Produk Profit A',
            'harga_beli' => 20,
            'is_active' => true,
        ]);
        $this->productB = MasterObatModel::create([
            'kode_obat' => 'PROFIT-B',
            'nama_obat' => 'Produk Profit B',
            'harga_beli' => 10,
            'is_active' => true,
        ]);

        $batchA = $this->stockBatch($this->productA, 'BATCH-A', 9, 20);
        $batchB = $this->stockBatch($this->productB, 'BATCH-B', 9, 10);
        $saleA = $this->sale($this->productA, $batchA, 'SALE-PROFIT-A', '2026-08-10 10:00:00', 2, 100, 20);
        $this->sale($this->productB, $batchB, 'SALE-PROFIT-B', '2026-08-12 10:00:00', 1, 80, 10);
        $this->salesReturn($saleA['transaction'], $saleA['detail'], $saleA['batch']);

        $this->movement($this->productA, $batchA, '2026-07-31 23:00:00', 10, 20, 10, 0, 'stok_awal');
        $this->movement($this->productA, $batchA, '2026-08-10 10:00:00', 8, 20, 0, 2, 'penjualan');
        $this->movement($this->productA, $batchA, '2026-08-10 12:00:00', 9, 20, 1, 0, 'retur_penjualan');
        $this->movement($this->productB, $batchB, '2026-07-31 23:00:00', 10, 10, 10, 0, 'stok_awal');
        $this->movement($this->productB, $batchB, '2026-08-12 10:00:00', 9, 10, 0, 1, 'penjualan');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_profitability_page_exposes_the_four_analysis_sections(): void
    {
        $this->actingAs($this->user)
            ->get(route('analisisProfitabilitas.index'))
            ->assertOk()
            ->assertSee('Analisis Profitabilitas')
            ->assertSee('Profit per Produk')
            ->assertSee('Margin Produk')
            ->assertSee('GMROI Produk')
            ->assertSee('Top Profit Product')
            ->assertSee("document.getElementById('profitabilityAnalysisApp')", false);
    }

    public function test_product_profit_margin_gmroi_and_top_profit_share_one_dataset(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('analisisProfitabilitas.data', [
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-31',
        ]));

        $response->assertOk()
            ->assertJsonPath('analysis.meta.branch_label', 'Cabang Profit')
            ->assertJsonPath('analysis.summary.net_sales', 150)
            ->assertJsonPath('analysis.summary.net_hpp', 30)
            ->assertJsonPath('analysis.summary.gross_profit', 120)
            ->assertJsonPath('analysis.summary.gross_margin', 80)
            ->assertJsonPath('analysis.summary.average_inventory_cost', 285)
            ->assertJsonPath('analysis.summary.gmroi', 0.4211)
            ->assertJsonPath('analysis.rows.0.name', 'Produk Profit B')
            ->assertJsonPath('analysis.rows.0.net_sales', 80)
            ->assertJsonPath('analysis.rows.0.net_hpp', 10)
            ->assertJsonPath('analysis.rows.0.gross_profit', 70)
            ->assertJsonPath('analysis.rows.0.gross_margin', 87.5)
            ->assertJsonPath('analysis.rows.0.opening_inventory_cost', 100)
            ->assertJsonPath('analysis.rows.0.closing_inventory_cost', 90)
            ->assertJsonPath('analysis.rows.0.average_inventory_cost', 95)
            ->assertJsonPath('analysis.rows.0.gmroi', 0.7368)
            ->assertJsonPath('analysis.rows.1.name', 'Produk Profit A')
            ->assertJsonPath('analysis.rows.1.returns_value', 30)
            ->assertJsonPath('analysis.rows.1.return_hpp', 20)
            ->assertJsonPath('analysis.rows.1.gross_profit', 50)
            ->assertJsonPath('analysis.rows.1.gmroi', 0.2632)
            ->assertJsonPath('analysis.top_profit_products.0.name', 'Produk Profit B')
            ->assertJsonPath('analysis.margin_leaders.0.name', 'Produk Profit B')
            ->assertJsonPath('analysis.gmroi_leaders.0.name', 'Produk Profit B');
    }

    public function test_existing_profit_report_uses_the_same_profitability_engine(): void
    {
        $this->actingAs($this->user)->getJson(route('laporan.penjualan.data', [
            'report' => 'keuntungan',
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-31',
        ]))->assertOk()
            ->assertJsonPath('report.metrics.0.value', 150)
            ->assertJsonPath('report.metrics.1.value', 30)
            ->assertJsonPath('report.metrics.2.value', 120)
            ->assertJsonPath('report.metrics.3.value', 80);
    }

    public function test_inventory_without_sales_remains_in_the_gmroi_denominator(): void
    {
        $medicine = MasterObatModel::create([
            'kode_obat' => 'PROFIT-IDLE',
            'nama_obat' => 'Produk Modal Mengendap',
            'harga_beli' => 30,
            'is_active' => true,
        ]);
        $batch = $this->stockBatch($medicine, 'BATCH-IDLE', 5, 30);
        $this->movement($medicine, $batch, '2026-07-31 22:00:00', 5, 30, 5, 0, 'stok_awal');

        $this->actingAs($this->user)->getJson(route('analisisProfitabilitas.data', [
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-31',
            'search' => 'Modal Mengendap',
        ]))->assertOk()
            ->assertJsonPath('analysis.summary.net_sales', 0)
            ->assertJsonPath('analysis.summary.gross_profit', 0)
            ->assertJsonPath('analysis.summary.average_inventory_cost', 150)
            ->assertJsonPath('analysis.summary.gmroi', 0)
            ->assertJsonPath('analysis.rows.0.name', 'Produk Modal Mengendap')
            ->assertJsonPath('analysis.rows.0.average_inventory_cost', 150)
            ->assertJsonPath('analysis.rows.0.gmroi', 0);
    }

    public function test_branch_scope_and_profit_sort_filters_are_enforced(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'PROFIT-02',
            'name' => 'Cabang Asing',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->getJson(route('analisisProfitabilitas.data', [
            'branch_id' => $foreignBranch->id,
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-31',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');

        $this->getJson(route('analisisProfitabilitas.data', [
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-31',
            'sort' => 'gross_profit',
            'direction' => 'asc',
            'top' => '10',
        ]))->assertOk()
            ->assertJsonPath('analysis.rows.0.name', 'Produk Profit A')
            ->assertJsonPath('analysis.meta.total_products', 2)
            ->assertJsonPath('analysis.meta.displayed_products', 2);
    }

    private function stockBatch(MasterObatModel $medicine, string $number, float $qty, float $cost): StokBatchModel
    {
        return StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => $number,
            'expired_date' => '2027-08-31',
            'qty' => $qty,
            'harga_beli' => $cost,
            'harga_jual' => 100,
            'created_by' => $this->user->id,
        ]);
    }

    private function sale(MasterObatModel $medicine, StokBatchModel $stockBatch, string $number, string $date, float $qty, float $total, float $cost): array
    {
        $transaction = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => $number,
            'tanggal_transaksi' => $date,
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal_gross' => $total,
            'subtotal_net' => $total,
            'grand_total' => $total,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => $date,
        ]);
        $detail = PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'obat_id' => $medicine->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'qty_jual' => $qty,
            'qty_stok' => $qty,
            'harga_jual' => $total / $qty,
            'subtotal_gross' => $total,
            'subtotal_net' => $total,
            'total_line' => $total,
        ]);
        $batch = PenjualanTransactionBatchModel::create([
            'penjualan_transaction_detail_id' => $detail->id,
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'stok_batch_id' => $stockBatch->id,
            'no_batch' => $stockBatch->no_batch,
            'qty_stok' => $qty,
            'harga_beli' => $cost,
            'harga_jual' => $total / $qty,
            'subtotal_gross' => $total,
        ]);

        return compact('transaction', 'detail', 'batch');
    }

    private function salesReturn(PenjualanTransactionModel $transaction, PenjualanTransactionDetailModel $detail, PenjualanTransactionBatchModel $batch): void
    {
        $return = ReturPenjualanModel::create([
            'branch_id' => $this->branch->id,
            'penjualan_transaction_id' => $transaction->id,
            'nomor_retur' => 'RETURN-PROFIT-A',
            'tanggal_retur' => '2026-08-10',
            'status' => 'posted',
            'total_item' => 1,
            'total_qty' => 1,
            'grand_total' => 30,
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => '2026-08-10 12:00:00',
        ]);
        $returnDetail = ReturPenjualanDetailModel::create([
            'retur_penjualan_id' => $return->id,
            'penjualan_transaction_detail_id' => $detail->id,
            'obat_id' => $this->productA->id,
            'kode_obat' => $this->productA->kode_obat,
            'nama_obat' => $this->productA->nama_obat,
            'qty_jual' => 1,
            'qty_stok' => 1,
            'harga_jual' => 30,
            'subtotal_gross' => 30,
            'total' => 30,
        ]);
        ReturPenjualanBatchModel::create([
            'retur_penjualan_detail_id' => $returnDetail->id,
            'penjualan_transaction_batch_id' => $batch->id,
            'branch_id' => $this->branch->id,
            'obat_id' => $this->productA->id,
            'stok_batch_id' => $batch->stok_batch_id,
            'no_batch' => $batch->no_batch,
            'qty_stok' => 1,
        ]);
    }

    private function movement(MasterObatModel $medicine, StokBatchModel $batch, string $date, float $balance, float $cost, float $in, float $out, string $type): void
    {
        KartuStokModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'stok_batch_id' => $batch->id,
            'tanggal_mutasi' => $date,
            'jenis_mutasi' => $type,
            'qty_masuk' => $in,
            'qty_keluar' => $out,
            'saldo_batch' => $balance,
            'saldo_total' => $balance,
            'no_batch' => $batch->no_batch,
            'harga_beli' => $cost,
            'created_by' => $this->user->id,
        ]);
    }
}
