<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\CategoryModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProcurementAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private DistributorModel $supplier;

    private MasterObatModel $medicine;

    private KonversiSatuanModel $conversion;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-31 15:00:00');

        $this->user = User::factory()->create(['name' => 'Apoteker Procurement']);
        $this->branch = BranchModel::create(['code' => 'PROC-01', 'name' => 'Cabang Procurement', 'is_active' => true]);
        $this->user->branches()->attach($this->branch->id);
        $this->supplier = DistributorModel::create(['kode' => 'SUP-PROC', 'nama' => 'Supplier Procurement', 'is_active' => true]);
        $unit = SatuansModel::create(['kode' => 'TAB-PROC', 'nama' => 'Tablet', 'is_active' => true]);
        $category = CategoryModel::create(['code' => 'AB-PROC', 'name' => 'Antibiotik']);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'AMOX-PROC',
            'nama_obat' => 'Amoxicillin 500 mg',
            'category_id' => $category->id,
            'satuan_id' => $unit->id,
            'stok_minimum' => 10,
            'harga_beli' => 1000,
            'is_active' => true,
        ]);
        $this->conversion = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id,
            'satuan_id' => $unit->id,
            'konversi' => 10,
            'is_default' => true,
        ]);

        StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $this->medicine->id,
            'no_batch' => 'PROC-BATCH-01',
            'expired_date' => '2028-08-31',
            'qty' => 20,
            'harga_beli' => 1000,
            'harga_jual' => 1800,
        ]);

        $current = $this->order('PO-PROC-CURRENT', '2026-08-25', 'approved', 10, 1000, 10000, 200, 300);
        $this->receipt($current['order_id'], $current['detail_id']);
        $this->order('PO-PROC-CANCEL', '2026-08-26', 'rejected', 2, 1000, 2000);
        $this->order('PO-PROC-PREVIOUS', '2026-07-20', 'approved', 5, 1000, 5000);
        $this->sale();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_procurement_workspace_requires_authentication_and_renders_requested_features(): void
    {
        $this->get(route('analisisPengadaan.index'))->assertRedirect(route('login'));

        $this->actingAs($this->user)->get(route('analisisPengadaan.index'))
            ->assertOk()
            ->assertSee('Analisis PO & Kebutuhan', false)
            ->assertSee('Perubahan Harga Estimasi PO')
            ->assertSee('Laporan realisasi')
            ->assertSee('Order vs Kebutuhan')
            ->assertSee('paKpiGrid', false)
            ->assertSee('paTrendChart', false)
            ->assertSee('paTopChart', false)
            ->assertSee('paMedicineDrawer', false)
            ->assertSee('Nilai Sudah Diterima')
            ->assertSee('Qty Sudah Diterima')
            ->assertSee('Qty Belum Diterima')
            ->assertSee('Item Sudah Diterima')
            ->assertSee('Item Belum Diterima')
            ->assertSee('paReceivedBody', false)
            ->assertSee('paReceivingFilterForm', false)
            ->assertSee('paReceivingSupplier', false)
            ->assertSee('paReceivingStatus', false)
            ->assertSee('paReceivingKpis', false)
            ->assertSee('data-receiving-table="received"', false)
            ->assertSee('data-receiving-table="outstanding"', false)
            ->assertSee('data-receiving-table="cancelled"', false)
            ->assertSee('data-table-filter="received"', false)
            ->assertSee('data-table-filter="outstanding"', false)
            ->assertSee('data-table-filter="cancelled"', false)
            ->assertSee('aria-label="Periode cepat Item Sudah Diterima"', false)
            ->assertSee('aria-label="Periode cepat Outstanding Order"', false)
            ->assertSee('aria-label="Periode cepat Order Dibatalkan"', false)
            ->assertSee('Qty diterima maksimum')
            ->assertSee('Qty belum diterima maksimum')
            ->assertSee('Jumlah item maksimum')
            ->assertSee('Sudah diterima (Posted)')
            ->assertSee('hanya menghitung PO berstatus Approved, Diterima Sebagian, dan Selesai.')
            ->assertSee('Analisis Supplier')
            ->assertSee('Order vs Penerimaan')
            ->assertSee('Order vs Penjualan');
    }

    public function test_dashboard_calculates_kpis_fulfillment_lead_time_cancellations_and_need_status(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', $this->period()))
            ->assertOk()
            ->assertJsonPath('analysis.meta.branch_label', 'Cabang Procurement')
            ->assertJsonPath('analysis.meta.methodology.value', 'Ringkasan, tren, supplier, dan outstanding memakai total estimasi PO termasuk biaya pengiriman dan asuransi. Analisis barang dan kategori tetap memakai subtotal item; pada filter item, biaya PO dialokasikan proporsional untuk ringkasan tingkat PO. Harga aktual berasal dari penerimaan posted dan tersedia pada Laporan Realisasi Pembelian.')
            ->assertJsonPath('analysis.summary.order_value', 10500)
            ->assertJsonPath('analysis.summary.item_value', 10000)
            ->assertJsonPath('analysis.summary.additional_cost_value', 500)
            ->assertJsonPath('analysis.summary.order_value_previous', 5000)
            ->assertJsonPath('analysis.summary.order_value_change', 110)
            ->assertJsonPath('analysis.summary.total_po', 1)
            ->assertJsonPath('analysis.summary.ordered_qty', 100)
            ->assertJsonPath('analysis.summary.received_value', 6000)
            ->assertJsonPath('analysis.summary.received_qty', 60)
            ->assertJsonPath('analysis.summary.outstanding_qty', 40)
            ->assertJsonPath('analysis.summary.total_items', 1)
            ->assertJsonPath('analysis.summary.received_items', 1)
            ->assertJsonPath('analysis.summary.outstanding_items', 1)
            ->assertJsonPath('analysis.summary.partial_received_items', 1)
            ->assertJsonPath('analysis.summary.received_value_previous', 0)
            ->assertJsonPath('analysis.summary.received_items_previous', 0)
            ->assertJsonPath('analysis.summary.outstanding_qty_previous', 50)
            ->assertJsonPath('analysis.summary.outstanding_value', 4200)
            ->assertJsonPath('analysis.summary.lead_time_days', 3)
            ->assertJsonPath('analysis.summary.active_suppliers', 1)
            ->assertJsonPath('analysis.medicines.0.name', 'Amoxicillin 500 mg')
            ->assertJsonPath('analysis.medicines.0.order_value', 10000)
            ->assertJsonPath('analysis.medicines.0.received_qty', 60)
            ->assertJsonPath('analysis.medicines.0.received_value', 6000)
            ->assertJsonPath('analysis.medicines.0.outstanding_qty', 40)
            ->assertJsonPath('analysis.medicines.0.fulfillment_percent', 60)
            ->assertJsonPath('analysis.medicines.0.sales_30_days', 40)
            ->assertJsonPath('analysis.medicines.0.need_status', 'over')
            ->assertJsonPath('analysis.categories.0.order_value', 10000)
            ->assertJsonPath('analysis.suppliers.0.order_value', 10500)
            ->assertJsonPath('analysis.suppliers.0.outstanding_value', 4200)
            ->assertJsonPath('analysis.suppliers.0.received_value', 6000)
            ->assertJsonPath('analysis.suppliers.0.received_qty', 60)
            ->assertJsonPath('analysis.suppliers.0.outstanding_qty', 40)
            ->assertJsonPath('analysis.suppliers.0.lead_time_days', 3)
            ->assertJsonCount(1, 'analysis.received_items')
            ->assertJsonPath('analysis.received_items.0.no_po', 'PO-PROC-CURRENT')
            ->assertJsonPath('analysis.received_items.0.medicine_code', 'AMOX-PROC')
            ->assertJsonPath('analysis.received_items.0.supplier_id', $this->supplier->id)
            ->assertJsonPath('analysis.received_items.0.received_qty', 60)
            ->assertJsonPath('analysis.received_items.0.outstanding_qty', 40)
            ->assertJsonPath('analysis.received_items.0.received_value', 6000)
            ->assertJsonPath('analysis.received_items.0.receipt_status', 'partial')
            ->assertJsonPath('analysis.cancelled_orders.0.no_po', 'PO-PROC-CANCEL')
            ->assertJsonPath('analysis.cancelled_orders.0.supplier_id', $this->supplier->id)
            ->assertJsonPath('analysis.outstanding.0.medicine_code', 'AMOX-PROC')
            ->assertJsonPath('analysis.outstanding.0.supplier_id', $this->supplier->id);

        $statuses = collect($response->json('analysis.status_distribution'))->keyBy('key');
        $this->assertSame(1, $statuses->get('approved')['count']);
        $this->assertSame(1, $statuses->get('rejected')['count']);
    }

    public function test_every_filter_and_medicine_detail_endpoint_use_authorized_branch_data(): void
    {
        $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', [
            ...$this->period(),
            'supplier_id' => $this->supplier->id,
            'medicine_id' => $this->medicine->id,
            'category_id' => $this->medicine->category_id,
            'po_status' => 'approved',
            'receipt_status' => 'partial',
            'created_by' => $this->user->id,
            'granularity' => 'week',
        ]))
            ->assertOk()
            ->assertJsonPath('analysis.summary.total_po', 1)
            ->assertJsonPath('analysis.meta.granularity', 'week')
            ->assertJsonPath('analysis.outstanding.0.outstanding_qty', 40);

        $this->getJson(route('analisisPengadaan.medicine', [
            'medicine' => $this->medicine->id,
            ...$this->period(),
        ]))
            ->assertOk()
            ->assertJsonPath('medicine.name', 'Amoxicillin 500 mg')
            ->assertJsonPath('medicine.summary.ordered_qty', 100)
            ->assertJsonPath('medicine.summary.received_qty', 60)
            ->assertJsonPath('medicine.summary.outstanding_qty', 40)
            ->assertJsonPath('medicine.summary.received_value', 6000)
            ->assertJsonPath('medicine.summary.order_count', 1)
            ->assertJsonPath('medicine.summary.main_supplier', 'Supplier Procurement')
            ->assertJsonPath('medicine.summary.last_price', 100)
            ->assertJsonPath('medicine.summary.sales_30_days', 40)
            ->assertJsonPath('medicine.history.1.received_qty', 60)
            ->assertJsonPath('medicine.history.1.outstanding_qty', 40)
            ->assertJsonPath('medicine.history.1.received_value', 6000)
            ->assertJsonCount(2, 'medicine.history');

        $otherBranch = BranchModel::create(['code' => 'PROC-02', 'name' => 'Cabang Terlarang', 'is_active' => true]);
        $this->getJson(route('analisisPengadaan.data', [...$this->period(), 'branch_id' => $otherBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');
    }

    public function test_receiving_summary_accumulates_posted_receipts_and_respects_status_and_branch_filters(): void
    {
        $current = DB::table('purchase_orders')->where('no_po', 'PO-PROC-CURRENT')->first();
        $detailId = (int) DB::table('purchase_order_details')->where('purchase_order_id', $current->id)->value('id');
        $this->receipt($current->id, $detailId, 2, 1500, 75);
        $this->receipt($current->id, $detailId, 4, 1000, 0, 'cancelled');

        $complete = $this->order('PO-PROC-COMPLETE', '2026-08-27', 'approved', 3, 1000, 3000);
        $this->receipt($complete['order_id'], $complete['detail_id'], 3, 1100, 25);
        $pending = $this->order('PO-PROC-NONE', '2026-08-27', 'approved', 4, 1000, 4000);
        $this->receipt($pending['order_id'], $pending['detail_id'], 4, 1000, 0, 'draft');
        $rejected = DB::table('purchase_orders')->where('no_po', 'PO-PROC-CANCEL')->first();
        $rejectedDetailId = (int) DB::table('purchase_order_details')->where('purchase_order_id', $rejected->id)->value('id');
        $this->receipt($rejected->id, $rejectedDetailId, 2);

        $otherBranch = BranchModel::create(['code' => 'PROC-OTHER', 'name' => 'Cabang Lain', 'is_active' => true]);
        $foreign = $this->order('PO-PROC-FOREIGN', '2026-08-27', 'approved', 6, 1000, 6000);
        DB::table('purchase_orders')->where('id', $foreign['order_id'])->update(['branch_id' => $otherBranch->id]);
        $this->receipt($foreign['order_id'], $foreign['detail_id']);

        $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', $this->period()))
            ->assertOk()
            ->assertJsonPath('analysis.summary.total_items', 3)
            ->assertJsonPath('analysis.summary.ordered_qty', 170)
            ->assertJsonPath('analysis.summary.received_qty', 110)
            ->assertJsonPath('analysis.summary.outstanding_qty', 60)
            ->assertJsonPath('analysis.summary.received_value', 12400)
            ->assertJsonPath('analysis.summary.received_items', 2)
            ->assertJsonPath('analysis.summary.outstanding_items', 2)
            ->assertJsonPath('analysis.summary.partial_received_items', 1)
            ->assertJsonCount(2, 'analysis.received_items')
            ->assertJsonPath('analysis.received_items.0.received_qty', 80)
            ->assertJsonPath('analysis.received_items.0.received_value', 9075)
            ->assertJsonPath('analysis.received_items.0.receipt_status', 'partial')
            ->assertJsonPath('analysis.received_items.1.receipt_status', 'complete')
            ->assertJsonCount(2, 'analysis.outstanding')
            ->assertJsonPath('analysis.medicines.0.received_value', 12400)
            ->assertJsonPath('analysis.suppliers.0.received_value', 12400);

        foreach (['none' => [0, 40, 0, 0, 1], 'partial' => [80, 20, 9075, 1, 1], 'complete' => [30, 0, 3325, 1, 0]] as $status => $expected) {
            $this->getJson(route('analisisPengadaan.data', [...$this->period(), 'receipt_status' => $status]))
                ->assertOk()
                ->assertJsonPath('analysis.summary.received_qty', $expected[0])
                ->assertJsonPath('analysis.summary.outstanding_qty', $expected[1])
                ->assertJsonPath('analysis.summary.received_value', $expected[2])
                ->assertJsonPath('analysis.summary.received_items', $expected[3])
                ->assertJsonPath('analysis.summary.outstanding_items', $expected[4]);
        }
    }

    public function test_received_value_uses_actual_discount_tax_and_cost_and_quantity_uses_receipt_conversion_snapshot(): void
    {
        DB::table('penerimaan_barang_detail')->update([
            'qty_diterima_stok' => 0,
            'konversi_satuan' => 5,
            'nilai_diskon' => 600,
            'nilai_ppn' => 594,
            'total' => 5994,
            'alokasi_biaya_lain' => 300,
        ]);

        $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', $this->period()))
            ->assertOk()
            ->assertJsonPath('analysis.summary.received_qty', 30)
            ->assertJsonPath('analysis.summary.outstanding_qty', 70)
            ->assertJsonPath('analysis.summary.received_value', 6294)
            ->assertJsonPath('analysis.received_items.0.received_value', 6294);
    }

    public function test_extra_quantity_on_one_line_does_not_hide_pending_quantity_on_another_line(): void
    {
        $order = DB::table('purchase_orders')->where('no_po', 'PO-PROC-CURRENT')->first();
        $detail = (array) DB::table('purchase_order_details')->where('purchase_order_id', $order->id)->first();
        unset($detail['id']);
        $detailId = DB::table('purchase_order_details')->insertGetId([...$detail, 'qty' => 1, 'subtotal' => 1000]);
        $this->receipt($order->id, $detailId, 20);

        $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', [...$this->period(), 'receipt_status' => 'partial']))
            ->assertOk()
            ->assertJsonPath('analysis.summary.total_po', 1)
            ->assertJsonPath('analysis.summary.received_qty', 260)
            ->assertJsonPath('analysis.summary.outstanding_qty', 40)
            ->assertJsonPath('analysis.summary.received_items', 2)
            ->assertJsonPath('analysis.summary.outstanding_items', 1)
            ->assertJsonPath('analysis.medicines.0.outstanding_qty', 40)
            ->assertJsonPath('analysis.suppliers.0.outstanding_qty', 40)
            ->assertJsonCount(1, 'analysis.outstanding');
    }

    public function test_receiving_metrics_are_zero_when_no_orders_match(): void
    {
        $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', [
            'date_start' => '2026-09-01', 'date_end' => '2026-09-30',
        ]))
            ->assertOk()
            ->assertJsonPath('analysis.summary.received_value', 0)
            ->assertJsonPath('analysis.summary.received_qty', 0)
            ->assertJsonPath('analysis.summary.outstanding_qty', 0)
            ->assertJsonPath('analysis.summary.received_items', 0)
            ->assertJsonPath('analysis.summary.outstanding_items', 0)
            ->assertJsonPath('analysis.summary.partial_received_items', 0)
            ->assertJsonCount(0, 'analysis.received_items')
            ->assertJsonCount(0, 'analysis.outstanding');
    }

    public function test_unapproved_and_rejected_orders_do_not_affect_analysis_or_previous_period(): void
    {
        foreach (['draft', 'waiting_approval', 'rejected'] as $status) {
            $current = $this->order('PO-EXCLUDED-'.$status, '2026-08-27', $status, 100, 5000, 500000, 1000, 1000);
            $this->receipt($current['order_id'], $current['detail_id'], 50, 5000, 1000);
            $previous = $this->order('PO-EXCLUDED-PREVIOUS-'.$status, '2026-07-27', $status, 100, 5000, 500000);
            $this->receipt($previous['order_id'], $previous['detail_id'], 50, 5000);
        }

        $response = $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', $this->period()))
            ->assertOk()
            ->assertJsonPath('analysis.summary.total_po', 1)
            ->assertJsonPath('analysis.summary.total_items', 1)
            ->assertJsonPath('analysis.summary.order_value', 10500)
            ->assertJsonPath('analysis.summary.item_value', 10000)
            ->assertJsonPath('analysis.summary.additional_cost_value', 500)
            ->assertJsonPath('analysis.summary.ordered_qty', 100)
            ->assertJsonPath('analysis.summary.received_qty', 60)
            ->assertJsonPath('analysis.summary.outstanding_qty', 40)
            ->assertJsonPath('analysis.summary.received_value', 6000)
            ->assertJsonPath('analysis.summary.outstanding_value', 4200)
            ->assertJsonPath('analysis.summary.received_items', 1)
            ->assertJsonPath('analysis.summary.outstanding_items', 1)
            ->assertJsonPath('analysis.summary.lead_time_days', 3)
            ->assertJsonPath('analysis.summary.total_po_previous', 1)
            ->assertJsonPath('analysis.summary.ordered_qty_previous', 50)
            ->assertJsonPath('analysis.summary.order_value_previous', 5000)
            ->assertJsonPath('analysis.summary.order_value_change', 110)
            ->assertJsonPath('analysis.summary.received_value_previous', 0)
            ->assertJsonPath('analysis.summary.outstanding_qty_previous', 50)
            ->assertJsonPath('analysis.medicines.0.order_count', 1)
            ->assertJsonPath('analysis.medicines.0.last_order_date', '2026-08-25')
            ->assertJsonPath('analysis.suppliers.0.order_value', 10500)
            ->assertJsonPath('analysis.categories.0.order_value', 10000)
            ->assertJsonPath('analysis.need_analysis.0.last_order_qty', 100)
            ->assertJsonCount(0, 'analysis.reorder')
            ->assertJsonCount(0, 'analysis.price_changes')
            ->assertJsonCount(1, 'analysis.received_items')
            ->assertJsonCount(1, 'analysis.outstanding');

        $this->assertEquals(10500, array_sum($response->json('analysis.trend.order_value')));
        $this->assertEquals(5000, array_sum($response->json('analysis.trend.previous_order_value')));
        $this->assertEquals(100, array_sum($response->json('analysis.trend.ordered_qty')));
        $this->assertEquals(60, array_sum($response->json('analysis.trend.received_qty')));

        $this->getJson(route('analisisPengadaan.medicine', ['medicine' => $this->medicine->id, ...$this->period()]))
            ->assertOk()
            ->assertJsonPath('medicine.summary.order_count', 1)
            ->assertJsonPath('medicine.summary.ordered_qty', 100)
            ->assertJsonPath('medicine.summary.order_value', 10000)
            ->assertJsonPath('medicine.summary.received_value', 6000)
            ->assertJsonPath('medicine.summary.outstanding_qty', 40)
            ->assertJsonCount(1, 'medicine.price_trend');

        foreach (['draft', 'waiting_approval', 'rejected'] as $status) {
            $filtered = $this->getJson(route('analisisPengadaan.data', [...$this->period(), 'po_status' => $status]))
                ->assertOk()
                ->assertJsonPath('analysis.summary.total_po', 0)
                ->assertJsonPath('analysis.summary.total_items', 0)
                ->assertJsonPath('analysis.summary.order_value', 0)
                ->assertJsonPath('analysis.summary.ordered_qty', 0)
                ->assertJsonPath('analysis.summary.received_qty', 0)
                ->assertJsonPath('analysis.summary.received_value', 0)
                ->assertJsonPath('analysis.summary.outstanding_qty', 0)
                ->assertJsonPath('analysis.summary.received_items', 0)
                ->assertJsonPath('analysis.summary.outstanding_items', 0)
                ->assertJsonPath('analysis.summary.total_po_previous', 0)
                ->assertJsonCount(0, 'analysis.medicines')
                ->assertJsonCount(0, 'analysis.suppliers')
                ->assertJsonCount(0, 'analysis.categories')
                ->assertJsonCount(0, 'analysis.need_analysis')
                ->assertJsonCount(0, 'analysis.received_items')
                ->assertJsonCount(0, 'analysis.outstanding');
            $this->assertEquals(0, array_sum($filtered->json('analysis.trend.order_value')));

            $this->getJson(route('analisisPengadaan.medicine', [
                'medicine' => $this->medicine->id, ...$this->period(), 'po_status' => $status,
            ]))
                ->assertOk()
                ->assertJsonPath('medicine.summary.order_count', 0)
                ->assertJsonPath('medicine.summary.ordered_qty', 0)
                ->assertJsonPath('medicine.summary.received_value', 0)
                ->assertJsonPath('medicine.summary.outstanding_qty', 0)
                ->assertJsonCount(0, 'medicine.price_trend');
        }
    }

    public function test_approved_partial_and_completed_orders_are_all_counted(): void
    {
        $partial = $this->order('PO-COUNTED-PARTIAL', '2026-08-26', 'diterima_sebagian', 4, 1000, 4000);
        $this->receipt($partial['order_id'], $partial['detail_id'], 2, 1200);
        $complete = $this->order('PO-COUNTED-COMPLETE', '2026-08-27', 'selesai', 3, 1000, 3000);
        $this->receipt($complete['order_id'], $complete['detail_id'], 3, 1100);

        $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', $this->period()))
            ->assertOk()
            ->assertJsonPath('analysis.summary.total_po', 3)
            ->assertJsonPath('analysis.summary.total_items', 3)
            ->assertJsonPath('analysis.summary.order_value', 17500)
            ->assertJsonPath('analysis.summary.ordered_qty', 170)
            ->assertJsonPath('analysis.summary.received_qty', 110)
            ->assertJsonPath('analysis.summary.outstanding_qty', 60)
            ->assertJsonPath('analysis.summary.received_value', 11700)
            ->assertJsonPath('analysis.summary.received_items', 3)
            ->assertJsonPath('analysis.summary.outstanding_items', 2)
            ->assertJsonPath('analysis.summary.partial_received_items', 2)
            ->assertJsonPath('analysis.medicines.0.order_count', 3)
            ->assertJsonCount(3, 'analysis.received_items')
            ->assertJsonCount(2, 'analysis.outstanding');

        foreach (['approved' => 100, 'diterima_sebagian' => 40, 'selesai' => 30] as $status => $qty) {
            $this->getJson(route('analisisPengadaan.data', [...$this->period(), 'po_status' => $status]))
                ->assertOk()
                ->assertJsonPath('analysis.summary.total_po', 1)
                ->assertJsonPath('analysis.summary.ordered_qty', $qty);
        }
    }

    public function test_receiving_tab_filters_supplier_and_item_status_with_matching_totals_without_changing_other_tabs(): void
    {
        $oneMed = DistributorModel::create(['kode' => 'SUP-ONEMED', 'nama' => 'OneMed', 'is_active' => true]);
        $complete = $this->order('PO-ONEMED-COMPLETE', '2026-08-27', 'approved', 3, 1000, 3000);
        $this->receipt($complete['order_id'], $complete['detail_id'], 3, 1100);
        $pending = $this->order('PO-ONEMED-PENDING', '2026-08-27', 'approved', 4, 1000, 4000);
        $this->receipt($pending['order_id'], $pending['detail_id'], 4, 1000, 0, 'draft');
        DB::table('purchase_orders')->whereIn('no_po', ['PO-PROC-CURRENT', 'PO-ONEMED-COMPLETE', 'PO-ONEMED-PENDING'])
            ->update(['distributor_id' => $oneMed->id]);
        $this->order('PO-OTHER-SUPPLIER', '2026-08-27', 'approved', 90, 1000, 90000);

        $cases = [
            '' => [3, 90, 80, 9300, 8200, 2, 2],
            'received' => [2, 90, 40, 9300, 4200, 2, 1],
            'outstanding' => [2, 60, 80, 6000, 8200, 1, 2],
            'none' => [1, 0, 40, 0, 4000, 0, 1],
            'partial' => [1, 60, 40, 6000, 4200, 1, 1],
            'complete' => [1, 30, 0, 3300, 0, 1, 0],
        ];
        foreach ($cases as $status => [$items, $receivedQty, $pendingQty, $receivedValue, $pendingValue, $receivedItems, $pendingItems]) {
            $response = $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', [
                ...$this->period(), 'receiving_supplier_id' => $oneMed->id, 'receiving_status' => $status,
            ]))
                ->assertOk()
                ->assertJsonPath('analysis.summary.total_po', 4)
                ->assertJsonPath('analysis.receiving.filters.supplier_id', $oneMed->id)
                ->assertJsonPath('analysis.receiving.summary.total_items', $items)
                ->assertJsonPath('analysis.receiving.summary.unique_items', 1)
                ->assertJsonPath('analysis.receiving.summary.received_qty', $receivedQty)
                ->assertJsonPath('analysis.receiving.summary.outstanding_qty', $pendingQty)
                ->assertJsonPath('analysis.receiving.summary.received_value', $receivedValue)
                ->assertJsonPath('analysis.receiving.summary.outstanding_value', $pendingValue)
                ->assertJsonPath('analysis.receiving.summary.received_items', $receivedItems)
                ->assertJsonPath('analysis.receiving.summary.outstanding_items', $pendingItems)
                ->assertJsonCount($receivedItems, 'analysis.receiving.received_items')
                ->assertJsonCount($pendingItems, 'analysis.receiving.outstanding')
                ->assertJsonCount(0, 'analysis.receiving.cancelled_orders');

            foreach ([...$response->json('analysis.receiving.received_items'), ...$response->json('analysis.receiving.outstanding')] as $row) {
                $this->assertSame('OneMed', $row['supplier']);
            }
        }
    }

    public function test_receiving_status_filters_each_item_even_when_another_item_on_the_po_has_been_received(): void
    {
        $order = DB::table('purchase_orders')->where('no_po', 'PO-PROC-CURRENT')->first();
        $detail = (array) DB::table('purchase_order_details')->where('purchase_order_id', $order->id)->first();
        unset($detail['id']);
        $pendingId = DB::table('purchase_order_details')->insertGetId([...$detail, 'qty' => 2, 'subtotal' => 2000]);

        $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', [
            ...$this->period(), 'receiving_status' => 'none', 'receiving_search' => '  amox-proc  ',
        ]))
            ->assertOk()
            ->assertJsonPath('analysis.summary.total_items', 2)
            ->assertJsonPath('analysis.receiving.filters.search', 'amox-proc')
            ->assertJsonPath('analysis.receiving.summary.total_items', 1)
            ->assertJsonPath('analysis.receiving.summary.received_qty', 0)
            ->assertJsonPath('analysis.receiving.summary.outstanding_qty', 20)
            ->assertJsonPath('analysis.receiving.outstanding.0.id', $pendingId)
            ->assertJsonCount(0, 'analysis.receiving.received_items');

        foreach (['po-proc-current', 'AMOXICILLIN', 'amox-proc'] as $search) {
            $this->getJson(route('analisisPengadaan.data', [...$this->period(), 'receiving_search' => $search]))
                ->assertOk()
                ->assertJsonPath('analysis.receiving.summary.total_items', 2)
                ->assertJsonPath('analysis.receiving.summary.unique_items', 1);
        }

        $this->getJson(route('analisisPengadaan.data', [...$this->period(), 'receiving_search' => 'tidak ditemukan']))
            ->assertOk()
            ->assertJsonPath('analysis.receiving.summary.total_items', 0)
            ->assertJsonPath('analysis.receiving.summary.received_value', 0)
            ->assertJsonPath('analysis.receiving.summary.outstanding_value', 0)
            ->assertJsonCount(0, 'analysis.receiving.outstanding');
    }

    public function test_receiving_filters_stay_within_authorized_branch_and_global_filter_scope(): void
    {
        $foreignSupplier = DistributorModel::create(['kode' => 'SUP-FOREIGN', 'nama' => 'Supplier Cabang Lain', 'is_active' => true]);
        $foreignBranch = BranchModel::create(['code' => 'PROC-FOREIGN', 'name' => 'Cabang Lain', 'is_active' => true]);
        $order = $this->order('PO-FOREIGN', '2026-08-27', 'approved', 10, 1000, 10000);
        DB::table('purchase_orders')->where('id', $order['order_id'])->update([
            'branch_id' => $foreignBranch->id, 'distributor_id' => $foreignSupplier->id,
        ]);
        $this->receipt($order['order_id'], $order['detail_id']);

        foreach ([
            ['receiving_supplier_id' => $foreignSupplier->id],
            ['supplier_id' => $foreignSupplier->id, 'receiving_supplier_id' => $this->supplier->id],
            ['receipt_status' => 'complete', 'receiving_status' => 'received'],
            ['date_start' => '2026-09-01', 'date_end' => '2026-09-30'],
            ['po_status' => 'rejected'],
        ] as $filters) {
            $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', [...$this->period(), ...$filters]))
                ->assertOk()
                ->assertJsonPath('analysis.receiving.summary.total_items', 0)
                ->assertJsonPath('analysis.receiving.summary.received_qty', 0)
                ->assertJsonPath('analysis.receiving.summary.outstanding_qty', 0)
                ->assertJsonPath('analysis.receiving.summary.received_value', 0)
                ->assertJsonPath('analysis.receiving.summary.outstanding_value', 0)
                ->assertJsonCount(0, 'analysis.receiving.received_items')
                ->assertJsonCount(0, 'analysis.receiving.outstanding');
        }
    }

    public function test_receiving_filter_input_is_validated(): void
    {
        foreach ([
            'receiving_status' => 'draft',
            'receiving_supplier_id' => 'OneMed',
            'receiving_search' => str_repeat('a', 151),
        ] as $key => $value) {
            $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', [...$this->period(), $key => $value]))
                ->assertUnprocessable()->assertJsonValidationErrors($key);
        }
    }

    private function period(): array
    {
        return ['date_start' => '2026-08-01', 'date_end' => '2026-08-31'];
    }

    private function order(
        string $number,
        string $date,
        string $status,
        float $qty,
        float $price,
        float $subtotal,
        float $insuranceCost = 0,
        float $shippingCost = 0,
    ): array {
        $extendedStatus = in_array($status, ['diterima_sebagian', 'selesai'], true) && DB::getDriverName() === 'sqlite';
        if ($extendedStatus) {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }
        try {
            $orderId = DB::table('purchase_orders')->insertGetId([
                'no_po' => $number,
                'distributor_id' => $this->supplier->id,
                'branch_id' => $this->branch->id,
                'tanggal_po' => $date,
                'total_estimasi' => $subtotal + $insuranceCost + $shippingCost,
                'biaya_asuransi' => $insuranceCost,
                'biaya_pengiriman' => $shippingCost,
                'status' => $status,
                'created_by' => $this->user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } finally {
            if ($extendedStatus) {
                DB::statement('PRAGMA ignore_check_constraints = OFF');
            }
        }
        $detailId = DB::table('purchase_order_details')->insertGetId([
            'purchase_order_id' => $orderId,
            'obat_id' => $this->medicine->id,
            'qty' => $qty,
            'harga_estimasi' => $price,
            'diskon_1' => 0,
            'diskon_2' => 0,
            'diskon_3' => 0,
            'subtotal' => $subtotal,
            'satuan_konversi' => $this->conversion->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return compact('orderId', 'detailId') + ['order_id' => $orderId, 'detail_id' => $detailId];
    }

    private function receipt(
        int $orderId,
        int $detailId,
        float $qty = 6,
        float $price = 1000,
        float $additionalCost = 0,
        string $status = 'posted',
    ): void {
        $sequence = DB::table('penerimaan_barang')->count() + 1;
        $receiptId = DB::table('penerimaan_barang')->insertGetId([
            'nomor_penerimaan' => 'RCV-PROC-'.$sequence,
            'purchase_order_id' => $orderId,
            'distributor_id' => $this->supplier->id,
            'nomor_faktur' => 'INV-PROC-001',
            'tanggal_penerimaan' => '2026-08-28',
            'total_barang' => 1,
            'total_qty' => $qty,
            'subtotal' => $qty * $price,
            'grand_total' => $qty * $price + $additionalCost,
            'status' => $status,
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => '2026-08-28 09:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('penerimaan_barang_detail')->insert([
            'penerimaan_barang_id' => $receiptId,
            'purchase_order_detail_id' => $detailId,
            'obat_id' => $this->medicine->id,
            'qty_po' => 10,
            'qty_diterima' => $qty,
            'qty_diterima_stok' => $qty * 10,
            'konversi_satuan' => 10,
            'satuan_beli' => 'Strip',
            'satuan_stok' => 'Tablet',
            'harga_beli' => $price,
            'harga_beli_stok' => $price / 10,
            'subtotal' => $qty * $price,
            'total' => $qty * $price,
            'alokasi_biaya_lain' => $additionalCost,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sale(): void
    {
        $sale = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'SALE-PROC-001',
            'tanggal_transaksi' => '2026-08-29 10:00:00',
            'status' => 'completed',
            'grand_total' => 80000,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => '2026-08-29 10:00:00',
        ]);
        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $sale->id,
            'obat_id' => $this->medicine->id,
            'nama_obat' => $this->medicine->nama_obat,
            'qty_jual' => 40,
            'qty_stok' => 40,
            'harga_jual' => 2000,
            'subtotal_gross' => 80000,
            'subtotal_net' => 80000,
            'total_line' => 80000,
        ]);
    }
}
