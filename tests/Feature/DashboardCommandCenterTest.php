<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\Penjualan\PenjualanPaymentModel;
use App\Models\Menu\Penjualan\PenjualanTransactionBatchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCommandCenterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private MasterObatModel $medicine;

    private StokBatchModel $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'CMD-01',
            'name' => 'Cabang Command Center',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);

        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'CMD-PARA',
            'nama_obat' => 'Paracetamol Command',
            'stok_minimum' => 5,
            'harga_beli' => 20000,
            'is_active' => true,
        ]);
        $this->batch = StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $this->medicine->id,
            'no_batch' => 'B-CMD-01',
            'expired_date' => today()->addDays(10),
            'qty' => 3,
            'harga_beli' => 20000,
            'harga_jual' => 50000,
        ]);
    }

    public function test_guest_is_redirected_from_command_center(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->getJson(route('dashboard.data'))->assertUnauthorized();
    }

    public function test_dashboard_renders_professional_command_center_workspace(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Command Center')
            ->assertSee('Pusat alert')
            ->assertSee('ccRevenueChart', false)
            ->assertSee('ccInventoryChart', false)
            ->assertSee('ccActionCenter', false)
            ->assertSee('ccAnalysisGrid', false)
            ->assertSee('materialdesignicons.min.css', false);
    }

    public function test_dashboard_calculates_scoped_financial_inventory_and_alert_data(): void
    {
        $sale = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'PJ-CMD-001',
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
            'payment_status' => 'paid',
            'customer_name' => 'Pasien Dashboard',
            'grand_total' => 100000,
            'total_bayar' => 100000,
            'kembalian' => 0,
            'sisa_tagihan' => 0,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => now(),
        ]);
        $detail = PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $sale->id,
            'obat_id' => $this->medicine->id,
            'kode_obat' => $this->medicine->kode_obat,
            'nama_obat' => $this->medicine->nama_obat,
            'satuan_jual' => 'Strip',
            'qty_jual' => 2,
            'qty_stok' => 2,
            'harga_jual' => 50000,
            'subtotal_gross' => 100000,
            'subtotal_net' => 100000,
            'total_line' => 100000,
        ]);
        PenjualanTransactionBatchModel::create([
            'penjualan_transaction_detail_id' => $detail->id,
            'branch_id' => $this->branch->id,
            'obat_id' => $this->medicine->id,
            'stok_batch_id' => $this->batch->id,
            'no_batch' => $this->batch->no_batch,
            'expired_date' => $this->batch->expired_date,
            'qty_stok' => 2,
            'harga_beli' => 20000,
            'harga_jual' => 50000,
            'subtotal_gross' => 100000,
        ]);
        PenjualanPaymentModel::create([
            'penjualan_transaction_id' => $sale->id,
            'metode' => 'tunai',
            'amount' => 100000,
            'paid_at' => now(),
            'received_by' => $this->user->id,
        ]);

        $foreignBranch = BranchModel::create([
            'code' => 'CMD-FOREIGN',
            'name' => 'Cabang Tidak Boleh Terlihat',
            'is_active' => true,
        ]);
        PenjualanTransactionModel::create([
            'branch_id' => $foreignBranch->id,
            'nomor_transaksi' => 'PJ-CMD-FOREIGN',
            'tanggal_transaksi' => now(),
            'status' => 'completed',
            'payment_status' => 'paid',
            'grand_total' => 900000,
            'total_bayar' => 900000,
            'sisa_tagihan' => 0,
            'created_by' => $this->user->id,
        ]);

        $distributor = DistributorModel::create([
            'kode' => 'DST-CMD',
            'nama' => 'Distributor Dashboard',
            'is_active' => true,
        ]);
        PembelianModel::create([
            'no_po' => 'PO-CMD-PENDING',
            'distributor_id' => $distributor->id,
            'branch_id' => $this->branch->id,
            'tanggal_po' => today(),
            'total_estimasi' => 250000,
            'status' => 'waiting_approval',
            'created_by' => $this->user->id,
        ]);
        $approvedOrder = PembelianModel::create([
            'no_po' => 'PO-CMD-INVOICE',
            'distributor_id' => $distributor->id,
            'branch_id' => $this->branch->id,
            'tanggal_po' => today()->subDays(7),
            'total_estimasi' => 500000,
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);
        PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-CMD-001',
            'purchase_order_id' => $approvedOrder->id,
            'distributor_id' => $distributor->id,
            'nomor_faktur' => 'INV-CMD-OVERDUE',
            'tanggal_penerimaan' => today()->subDays(5),
            'tanggal_jatuh_tempo' => today()->subDay(),
            'total_faktur' => 500000,
            'grand_total' => 500000,
            'status' => 'posted',
            'status_pembayaran' => 'sebagian',
            'jumlah_dibayar' => 300000,
            'sisa_hutang' => 200000,
            'posted_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('dashboard.data', ['period' => '30']));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('dashboard.meta.branch_label', $this->branch->name)
            ->assertJsonPath('dashboard.kpis.0.key', 'net_sales')
            ->assertJsonPath('dashboard.kpis.0.value', 100000)
            ->assertJsonPath('dashboard.kpis.1.key', 'gross_profit')
            ->assertJsonPath('dashboard.kpis.1.value', 60000)
            ->assertJsonPath('dashboard.kpis.2.value', 1)
            ->assertJsonPath('dashboard.inventory.near_expiry', 1)
            ->assertJsonPath('dashboard.inventory.stock_value', 60000)
            ->assertJsonCount(1, 'dashboard.branches')
            ->assertJsonPath('dashboard.branches.0.name', $this->branch->name)
            ->assertJsonPath('dashboard.top_products.0.name', $this->medicine->nama_obat)
            ->assertJsonPath('dashboard.payment_mix.0.key', 'tunai')
            ->assertJsonPath('dashboard.payment_mix.0.value', 100000);

        $kinds = collect($response->json('dashboard.alerts.items'))->pluck('kind');
        $this->assertTrue($kinds->contains('near_expiry'));
        $this->assertTrue($kinds->contains('low_stock'));
        $this->assertTrue($kinds->contains('payable'));
        $this->assertTrue($kinds->contains('approval'));
        $this->assertFalse(collect($response->json('dashboard.branches'))->pluck('name')->contains($foreignBranch->name));
    }

    public function test_user_cannot_filter_dashboard_to_an_unassigned_branch(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'CMD-NO-ACCESS',
            'name' => 'Cabang Tanpa Akses',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('dashboard.data', ['branch_id' => $foreignBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');
    }
}
