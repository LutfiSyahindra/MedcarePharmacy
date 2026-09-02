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

        $current = $this->order('PO-PROC-CURRENT', '2026-08-25', 'approved', 10, 1000, 10000);
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
            ->assertSee('Analisis Supplier')
            ->assertSee('Order vs Penerimaan')
            ->assertSee('Order vs Penjualan');
    }

    public function test_dashboard_calculates_kpis_fulfillment_lead_time_cancellations_and_need_status(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('analisisPengadaan.data', $this->period()))
            ->assertOk()
            ->assertJsonPath('analysis.meta.branch_label', 'Cabang Procurement')
            ->assertJsonPath('analysis.meta.methodology.value', 'Nilai dan harga pada analisis berasal dari estimasi item PO. Harga aktual berasal dari penerimaan posted dan tersedia pada Laporan Realisasi Pembelian.')
            ->assertJsonPath('analysis.summary.order_value', 10000)
            ->assertJsonPath('analysis.summary.order_value_previous', 5000)
            ->assertJsonPath('analysis.summary.order_value_change', 100)
            ->assertJsonPath('analysis.summary.total_po', 1)
            ->assertJsonPath('analysis.summary.ordered_qty', 100)
            ->assertJsonPath('analysis.summary.outstanding_value', 4000)
            ->assertJsonPath('analysis.summary.lead_time_days', 3)
            ->assertJsonPath('analysis.summary.active_suppliers', 1)
            ->assertJsonPath('analysis.medicines.0.name', 'Amoxicillin 500 mg')
            ->assertJsonPath('analysis.medicines.0.received_qty', 60)
            ->assertJsonPath('analysis.medicines.0.fulfillment_percent', 60)
            ->assertJsonPath('analysis.medicines.0.sales_30_days', 40)
            ->assertJsonPath('analysis.medicines.0.need_status', 'over')
            ->assertJsonPath('analysis.suppliers.0.lead_time_days', 3)
            ->assertJsonPath('analysis.cancelled_orders.0.no_po', 'PO-PROC-CANCEL');

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
            ->assertJsonPath('medicine.summary.order_count', 1)
            ->assertJsonPath('medicine.summary.main_supplier', 'Supplier Procurement')
            ->assertJsonPath('medicine.summary.last_price', 100)
            ->assertJsonPath('medicine.summary.sales_30_days', 40)
            ->assertJsonCount(2, 'medicine.history');

        $otherBranch = BranchModel::create(['code' => 'PROC-02', 'name' => 'Cabang Terlarang', 'is_active' => true]);
        $this->getJson(route('analisisPengadaan.data', [...$this->period(), 'branch_id' => $otherBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');
    }

    private function period(): array
    {
        return ['date_start' => '2026-08-01', 'date_end' => '2026-08-31'];
    }

    private function order(string $number, string $date, string $status, float $qty, float $price, float $subtotal): array
    {
        $orderId = DB::table('purchase_orders')->insertGetId([
            'no_po' => $number,
            'distributor_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'tanggal_po' => $date,
            'total_estimasi' => $subtotal,
            'status' => $status,
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

    private function receipt(int $orderId, int $detailId): void
    {
        $receiptId = DB::table('penerimaan_barang')->insertGetId([
            'nomor_penerimaan' => 'RCV-PROC-001',
            'purchase_order_id' => $orderId,
            'distributor_id' => $this->supplier->id,
            'nomor_faktur' => 'INV-PROC-001',
            'tanggal_penerimaan' => '2026-08-28',
            'total_barang' => 1,
            'total_qty' => 6,
            'subtotal' => 6000,
            'grand_total' => 6000,
            'status' => 'posted',
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
            'qty_diterima' => 6,
            'qty_diterima_stok' => 60,
            'konversi_satuan' => 10,
            'satuan_beli' => 'Strip',
            'satuan_stok' => 'Tablet',
            'harga_beli' => 1000,
            'harga_beli_stok' => 100,
            'subtotal' => 6000,
            'total' => 6000,
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
