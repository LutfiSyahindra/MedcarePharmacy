<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\CategoryModel;
use App\Models\GolonganModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Penjualan\CashierShiftModel;
use App\Models\Menu\Penjualan\PenjualanPaymentModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Penjualan\ReturPenjualanDetailModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RevenueAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private MasterObatModel $medicine;

    private CashierShiftModel $shift;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-31 14:00:00');

        $this->user = User::factory()->create(['name' => 'Kasir Omzet']);
        $this->branch = BranchModel::create(['code' => 'OMZ-01', 'name' => 'Cabang Omzet', 'is_active' => true]);
        $this->user->branches()->attach($this->branch->id);
        $category = CategoryModel::create(['code' => 'VIT', 'name' => 'Vitamin']);
        $classification = GolonganModel::create(['kode' => 'BBS', 'nama' => 'Obat Bebas', 'is_active' => true]);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'VIT-OMZ-01',
            'nama_obat' => 'Vitamin Omzet',
            'category_id' => $category->id,
            'golongan_id' => $classification->id,
            'is_active' => true,
        ]);
        $this->shift = CashierShiftModel::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'shift_number' => 'SHIFT-OMZ-001',
            'status' => 'closed',
            'opening_amount' => 100000,
            'opened_at' => '2026-08-31 07:00:00',
            'closed_at' => '2026-08-31 15:00:00',
        ]);

        $sale = $this->sale('SALE-OMZ-CURRENT', '2026-08-31 10:15:00', 100000, 110000, 5000, 5000);
        $detail = $this->detail($sale, 2, 110000, 5000, 105000);
        PenjualanPaymentModel::create([
            'penjualan_transaction_id' => $sale->id,
            'metode' => 'qris',
            'amount' => 40000,
            'paid_at' => '2026-08-31 10:15:00',
        ]);
        PenjualanPaymentModel::create([
            'penjualan_transaction_id' => $sale->id,
            'metode' => 'tunai',
            'amount' => 60000,
            'paid_at' => '2026-08-31 10:15:00',
        ]);

        $return = ReturPenjualanModel::create([
            'branch_id' => $this->branch->id,
            'penjualan_transaction_id' => $sale->id,
            'nomor_retur' => 'RET-OMZ-001',
            'tanggal_retur' => '2026-08-31',
            'status' => 'posted',
            'total_item' => 1,
            'total_qty' => 1,
            'grand_total' => 20000,
            'posted_at' => '2026-08-31 12:00:00',
        ]);
        ReturPenjualanDetailModel::create([
            'retur_penjualan_id' => $return->id,
            'penjualan_transaction_detail_id' => $detail->id,
            'obat_id' => $this->medicine->id,
            'kode_obat' => $this->medicine->kode_obat,
            'nama_obat' => $this->medicine->nama_obat,
            'qty_jual' => 1,
            'subtotal_gross' => 20000,
            'total' => 20000,
        ]);

        $previous = $this->sale('SALE-OMZ-PREVIOUS', '2026-07-31 09:00:00', 50000, 50000, 0, 0);
        $this->detail($previous, 1, 50000, 0, 50000);
        PenjualanPaymentModel::create([
            'penjualan_transaction_id' => $previous->id,
            'metode' => 'qris',
            'amount' => 50000,
            'paid_at' => '2026-07-31 09:00:00',
        ]);

        $cancelled = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'SALE-OMZ-CANCELLED',
            'tanggal_transaksi' => '2026-08-31 11:00:00',
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'cancelled',
            'grand_total' => 999000,
            'completed_by' => $this->user->id,
            'cancelled_at' => '2026-08-31 11:05:00',
        ]);
        $this->detail($cancelled, 10, 999000, 0, 999000);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_requires_authentication_and_renders_complete_workspace(): void
    {
        $this->get(route('analisisPenjualan.index'))->assertRedirect(route('login'));

        $this->actingAs($this->user)->get(route('analisisPenjualan.index'))
            ->assertOk()
            ->assertSee('Analisis Penjualan')
            ->assertSee('roKpiGrid', false)
            ->assertSee('roTrendChart', false)
            ->assertSee('roProductBody', false)
            ->assertSee('roFastMovingBody', false)
            ->assertSee('roMarketBasketBody', false)
            ->assertSee('Target vs Realisasi Omzet');

        $this->actingAs($this->user)->get(route('analisisOmzet.index'))
            ->assertOk()
            ->assertSee('Analisis Penjualan');
    }

    public function test_analysis_calculates_net_revenue_growth_breakdowns_and_excludes_cancelled_sales(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('analisisOmzet.data', $this->period()))
            ->assertOk()
            ->assertJsonPath('analysis.meta.branch_label', 'Cabang Omzet')
            ->assertJsonPath('analysis.summary.total_revenue', 100000)
            ->assertJsonPath('analysis.summary.net_revenue', 80000)
            ->assertJsonPath('analysis.summary.transactions', 1)
            ->assertJsonPath('analysis.summary.qty_sold', 2)
            ->assertJsonPath('analysis.summary.average_transaction', 100000)
            ->assertJsonPath('analysis.summary.total_discount', 10000)
            ->assertJsonPath('analysis.summary.return_value', 20000)
            ->assertJsonPath('analysis.summary.growth_percent', 60)
            ->assertJsonPath('analysis.products.0.name', 'Vitamin Omzet')
            ->assertJsonPath('analysis.products.0.revenue', 80000)
            ->assertJsonPath('analysis.products.0.contribution_percent', 100)
            ->assertJsonPath('analysis.products.0.growth_percent', 60)
            ->assertJsonPath('analysis.categories.0.label', 'Vitamin')
            ->assertJsonPath('analysis.hourly.peak_hour', '10:00')
            ->assertJsonPath('analysis.hourly.busy_hour', '10:00')
            ->assertJsonPath('analysis.weekdays.peak_day', 'Senin')
            ->assertJsonPath('analysis.cashiers.0.name', 'Kasir Omzet')
            ->assertJsonPath('analysis.fast_moving.rows.0.name', 'Vitamin Omzet')
            ->assertJsonPath('analysis.fast_moving.rows.0.net_qty', 1)
            ->assertJsonPath('analysis.market_basket.summary.transactions_analyzed', 1)
            ->assertJsonCount(0, 'analysis.market_basket.rows');

        $payments = collect($response->json('analysis.payments'))->keyBy('key');
        $this->assertSame(32000.0, (float) $payments->get('qris')['revenue']);
        $this->assertSame(48000.0, (float) $payments->get('tunai')['revenue']);
        $this->assertSame(40.0, (float) $payments->get('qris')['contribution_percent']);
    }

    public function test_fast_moving_product_ranking_and_market_basket_metrics_are_available(): void
    {
        $fastMedicine = MasterObatModel::create([
            'kode_obat' => 'FAST-001',
            'nama_obat' => 'Produk Cepat',
            'category_id' => $this->medicine->category_id,
            'golongan_id' => $this->medicine->golongan_id,
            'is_active' => true,
        ]);
        $basketSale = $this->sale('SALE-BASKET-001', '2026-08-30 11:00:00', 60000, 60000, 0, 0);
        $this->detail($basketSale, 1, 10000, 0, 10000);
        $this->detail($basketSale, 5, 50000, 0, 50000, $fastMedicine);

        $response = $this->actingAs($this->user)->getJson(route('analisisPenjualan.data', [
            ...$this->period(),
            'product_metric' => 'qty',
        ]))
            ->assertOk()
            ->assertJsonPath('analysis.meta.product_metric', 'qty')
            ->assertJsonPath('analysis.products.0.name', 'Produk Cepat')
            ->assertJsonPath('analysis.fast_moving.rows.0.name', 'Produk Cepat')
            ->assertJsonPath('analysis.fast_moving.rows.0.net_qty', 5)
            ->assertJsonPath('analysis.market_basket.summary.transactions_analyzed', 2)
            ->assertJsonPath('analysis.market_basket.summary.pairs_found', 1)
            ->assertJsonPath('analysis.market_basket.rows.0.product_a.name', 'Vitamin Omzet')
            ->assertJsonPath('analysis.market_basket.rows.0.product_b.name', 'Produk Cepat')
            ->assertJsonPath('analysis.market_basket.rows.0.pair_transactions', 1)
            ->assertJsonPath('analysis.market_basket.rows.0.support_percent', 50)
            ->assertJsonPath('analysis.market_basket.rows.0.confidence_a_to_b_percent', 50)
            ->assertJsonPath('analysis.market_basket.rows.0.confidence_b_to_a_percent', 100)
            ->assertJsonPath('analysis.market_basket.rows.0.lift', 1);

        $this->assertSame('Netral', $response->json('analysis.market_basket.rows.0.strength'));
    }

    public function test_every_filter_applies_to_kpis_and_payment_filter_allocates_split_transaction_value(): void
    {
        $this->actingAs($this->user)->getJson(route('analisisOmzet.data', [
            ...$this->period(),
            'medicine_id' => $this->medicine->id,
            'category_id' => $this->medicine->category_id,
            'golongan_id' => $this->medicine->golongan_id,
            'cashier_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'transaction_type' => 'penjualan_bebas',
            'payment_method' => 'qris',
        ]))
            ->assertOk()
            ->assertJsonPath('analysis.summary.total_revenue', 40000)
            ->assertJsonPath('analysis.summary.return_value', 8000)
            ->assertJsonPath('analysis.summary.net_revenue', 32000)
            ->assertJsonPath('analysis.summary.transactions', 1)
            ->assertJsonCount(1, 'analysis.payments')
            ->assertJsonPath('analysis.payments.0.key', 'qris')
            ->assertJsonPath('analysis.payments.0.revenue', 32000);
    }

    public function test_trend_supports_daily_weekly_monthly_and_yearly_comparison_series(): void
    {
        foreach (['day', 'week', 'month', 'year'] as $granularity) {
            $response = $this->actingAs($this->user)->getJson(route('analisisOmzet.data', [
                ...$this->period(),
                'granularity' => $granularity,
            ]))->assertOk()->assertJsonPath('analysis.trend.granularity', $granularity);

            $trend = $response->json('analysis.trend');
            $this->assertNotEmpty($trend['labels']);
            $this->assertCount(count($trend['labels']), $trend['current_revenue']);
            $this->assertCount(count($trend['labels']), $trend['previous_revenue']);
            $this->assertCount(count($trend['labels']), $trend['transactions']);
        }
    }

    public function test_target_can_be_saved_per_branch_and_is_reflected_in_realization(): void
    {
        $this->actingAs($this->user)->postJson(route('analisisOmzet.target.store'), [
            'branch_id' => $this->branch->id,
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-31',
            'target_amount' => 100000,
        ])->assertOk()->assertJsonPath('target.amount', 100000);

        $this->getJson(route('analisisOmzet.data', [...$this->period(), 'branch_id' => $this->branch->id]))
            ->assertOk()
            ->assertJsonPath('analysis.target.amount', 100000)
            ->assertJsonPath('analysis.target.realization', 80000)
            ->assertJsonPath('analysis.target.achievement_percent', 80)
            ->assertJsonPath('analysis.target.remaining', 20000)
            ->assertJsonPath('analysis.target.status', 'Hampir Tercapai')
            ->assertJsonPath('analysis.target.can_edit', true);
    }

    public function test_access_scope_validation_and_exports_are_available(): void
    {
        $foreignBranch = BranchModel::create(['code' => 'OMZ-X', 'name' => 'Cabang Lain', 'is_active' => true]);
        $this->actingAs($this->user)->getJson(route('analisisOmzet.data', [...$this->period(), 'branch_id' => $foreignBranch->id]))
            ->assertUnprocessable()->assertJsonValidationErrors('branch_id');

        $this->get(route('analisisOmzet.export.excel', $this->period()))
            ->assertOk()
            ->assertDownload('analisis-penjualan-20260801-20260831.xlsx');
        $this->get(route('analisisOmzet.export.pdf', $this->period()))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('analisis-penjualan-20260801-20260831.pdf');
    }

    private function period(): array
    {
        return ['date_start' => '2026-08-01', 'date_end' => '2026-08-31'];
    }

    private function sale(string $number, string $date, float $grandTotal, float $gross, float $itemDiscount, float $transactionDiscount): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'cashier_shift_id' => $this->shift->id,
            'nomor_transaksi' => $number,
            'tanggal_transaksi' => $date,
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal_gross' => $gross,
            'diskon_item_total' => $itemDiscount,
            'diskon_transaksi_nominal' => $transactionDiscount,
            'subtotal_net' => $grandTotal,
            'grand_total' => $grandTotal,
            'total_bayar' => $grandTotal,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => $date,
        ]);
    }

    private function detail(
        PenjualanTransactionModel $sale,
        float $qty,
        float $gross,
        float $discount,
        float $net,
        ?MasterObatModel $medicine = null,
    ): PenjualanTransactionDetailModel {
        $medicine ??= $this->medicine;

        return PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $sale->id,
            'obat_id' => $medicine->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'satuan_jual' => 'Tablet',
            'qty_jual' => $qty,
            'harga_jual' => $qty > 0 ? $gross / $qty : 0,
            'subtotal_gross' => $gross,
            'diskon_nominal' => $discount,
            'subtotal_net' => $net,
            'total_line' => $net,
        ]);
    }
}
