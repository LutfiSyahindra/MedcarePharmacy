<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\CategoryModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Penjualan\CashierShiftModel;
use App\Models\Menu\Penjualan\PenjualanPaymentModel;
use App\Models\Menu\Penjualan\PenjualanTransactionBatchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Penjualan\ReturPenjualanBatchModel;
use App\Models\Menu\Penjualan\ReturPenjualanDetailModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Models\User;
use App\Services\Menu\Laporan\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-28 14:30:00');

        $this->user = User::factory()->create(['name' => 'Kasir Laporan']);
        $this->branch = BranchModel::create([
            'code' => 'RPT-01',
            'name' => 'Cabang Laporan',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);

        $category = CategoryModel::create(['code' => 'ANL', 'name' => 'Analgesik']);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-RPT-01',
            'nama_obat' => 'Paracetamol Laporan',
            'category_id' => $category->id,
            'is_active' => true,
        ]);
        $shift = CashierShiftModel::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'shift_number' => 'SHIFT-RPT-001',
            'status' => 'closed',
            'opening_amount' => 100000,
            'cash_sales' => 100000,
            'expected_cash' => 200000,
            'actual_cash' => 200000,
            'cash_difference' => 0,
            'opened_at' => now()->subHours(6),
            'closed_at' => now()->subHour(),
        ]);

        $sale = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'cashier_shift_id' => $shift->id,
            'nomor_transaksi' => 'SALE-RPT-001',
            'tanggal_transaksi' => '2026-08-28 10:15:00',
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
            'payment_status' => 'paid',
            'customer_name' => 'Pelanggan Laporan',
            'subtotal_gross' => 110000,
            'diskon_item_total' => 5000,
            'diskon_transaksi_nominal' => 5000,
            'subtotal_net' => 100000,
            'grand_total' => 100000,
            'total_bayar' => 100000,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => '2026-08-28 10:15:00',
        ]);
        $detail = PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $sale->id,
            'obat_id' => $medicine->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'satuan_jual' => 'Tablet',
            'qty_jual' => 2,
            'harga_jual' => 55000,
            'subtotal_gross' => 110000,
            'diskon_nominal' => 5000,
            'subtotal_net' => 105000,
            'total_line' => 105000,
        ]);
        $saleBatch = PenjualanTransactionBatchModel::create([
            'penjualan_transaction_detail_id' => $detail->id,
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'BATCH-RPT-001',
            'qty_stok' => 2,
            'harga_beli' => 30000,
            'harga_jual' => 55000,
            'subtotal_gross' => 110000,
        ]);
        PenjualanPaymentModel::create([
            'penjualan_transaction_id' => $sale->id,
            'metode' => 'qris',
            'amount' => 100000,
            'reference_no' => 'QR-RPT-001',
            'paid_at' => '2026-08-28 10:15:00',
            'received_by' => $this->user->id,
        ]);

        $return = ReturPenjualanModel::create([
            'branch_id' => $this->branch->id,
            'penjualan_transaction_id' => $sale->id,
            'nomor_retur' => 'RET-RPT-001',
            'tanggal_retur' => '2026-08-28',
            'status' => 'posted',
            'refund_method' => 'qris',
            'total_item' => 1,
            'total_qty' => 1,
            'grand_total' => 20000,
            'alasan' => 'Kemasan rusak',
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => '2026-08-28 12:00:00',
        ]);
        $returnDetail = ReturPenjualanDetailModel::create([
            'retur_penjualan_id' => $return->id,
            'penjualan_transaction_detail_id' => $detail->id,
            'obat_id' => $medicine->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'satuan_jual' => 'Tablet',
            'qty_jual' => 1,
            'harga_jual' => 20000,
            'subtotal_gross' => 20000,
            'total' => 20000,
            'alasan_item' => 'Kemasan rusak',
        ]);
        ReturPenjualanBatchModel::create([
            'retur_penjualan_detail_id' => $returnDetail->id,
            'penjualan_transaction_batch_id' => $saleBatch->id,
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'BATCH-RPT-001',
            'qty_stok' => 1,
        ]);

        $cancelled = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'SALE-CANCEL-001',
            'tanggal_transaksi' => '2026-08-28 11:00:00',
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'grand_total' => 75000,
            'created_by' => $this->user->id,
            'cancelled_by' => $this->user->id,
            'cancelled_at' => '2026-08-28 11:05:00',
            'cancellation_reason' => 'Pasien membatalkan pesanan',
        ]);
        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $cancelled->id,
            'obat_id' => $medicine->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'satuan_jual' => 'Tablet',
            'qty_jual' => 3,
            'harga_jual' => 25000,
            'subtotal_gross' => 75000,
            'total_line' => 75000,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_sales_report_workspace_requires_authentication_and_exposes_all_report_views(): void
    {
        $this->get(route('laporan.penjualan.index', ['report' => 'ringkasan']))
            ->assertRedirect(route('login'));

        $response = $this->actingAs($this->user)
            ->get(route('laporan.penjualan.index', ['report' => 'ringkasan']));

        $response->assertOk()
            ->assertSee('Sales Intelligence')
            ->assertSee('13 perspektif laporan')
            ->assertSee('srMetricGrid', false)
            ->assertSee('srChart', false)
            ->assertSee('srTableBody', false)
            ->assertSee('srColumnToggle', false)
            ->assertSee('srDensityToggle', false)
            ->assertSee('srFocusTable', false)
            ->assertSee('srPageNumbers', false);

        foreach (SalesReportService::TYPES as $definition) {
            $response->assertSee($definition['short_title']);
        }

        $response->assertSee('Dashboard analisis')
            ->assertSee('Omzet laporan: completed sebelum retur');
    }

    public function test_summary_and_every_sales_report_type_return_scoped_operational_data(): void
    {
        $query = ['date_start' => '2026-08-01', 'date_end' => '2026-08-28'];
        $summary = $this->actingAs($this->user)->getJson(route('laporan.penjualan.data', ['report' => 'ringkasan', ...$query]));

        $summary->assertOk()
            ->assertJsonPath('report.meta.branch_label', $this->branch->name)
            ->assertJsonPath('report.meta.amount_basis', 'Omzet laporan berasal dari transaksi completed sebelum retur. Nilai setelah retur tersedia pada laporan keuntungan dan Analisis Penjualan.')
            ->assertJsonPath('report.metrics.0.value', 100000)
            ->assertJsonPath('report.metrics.1.value', 1)
            ->assertJsonPath('report.metrics.2.value', 2)
            ->assertJsonPath('report.metrics.3.value', 10000)
            ->assertJsonPath('report.insight.net_after_returns', 80000)
            ->assertJsonPath('report.table.rows.0.transactions', 1)
            ->assertJsonPath('report.table.rows.0.revenue', 100000);

        foreach (array_keys(SalesReportService::TYPES) as $type) {
            $response = $this->getJson(route('laporan.penjualan.data', ['report' => $type, ...$query]))
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('report.meta.type', $type)
                ->assertJsonStructure(['report' => ['metrics', 'chart' => ['labels', 'series'], 'table' => ['columns', 'rows', 'pagination']]]);

            $table = $response->json('report.table');
            $this->assertNotEmpty($table['rows'], "Laporan {$type} seharusnya memiliki data fixture.");

            foreach ($table['columns'] as $column) {
                $this->assertArrayHasKey(
                    $column['key'],
                    $table['rows'][0],
                    "Kolom {$column['key']} hilang dari baris laporan {$type}.",
                );
            }
        }

        $this->getJson(route('laporan.penjualan.data', ['report' => 'detail', ...$query]))
            ->assertJsonPath('report.table.rows.0.sale_date', '2026-08-28 10:15:00')
            ->assertJsonPath('report.table.rows.0.product_code', 'OBT-RPT-01')
            ->assertJsonPath('report.table.rows.0.product_name', 'Paracetamol Laporan')
            ->assertJsonPath('report.table.rows.0.transaction_type', 'penjualan_bebas')
            ->assertJsonPath('report.table.rows.0.qty', 2);
        $medicineReport = $this->getJson(route('laporan.penjualan.data', ['report' => 'obat', ...$query]))
            ->assertJsonPath('report.table.rows.0.product_code', 'OBT-RPT-01')
            ->assertJsonPath('report.table.rows.0.revenue', 100000);
        $medicineColumns = collect($medicineReport->json('report.table.columns'))->pluck('key')->all();
        $this->assertNotContains('hpp', $medicineColumns);
        $this->assertNotContains('gross_profit', $medicineColumns);

        $this->getJson(route('laporan.penjualan.data', ['report' => 'keuntungan', ...$query]))
            ->assertJsonPath('report.metrics.0.value', 80000)
            ->assertJsonPath('report.metrics.1.value', 30000)
            ->assertJsonPath('report.metrics.2.value', 50000)
            ->assertJsonPath('report.metrics.3.value', 62.5)
            ->assertJsonPath('report.table.rows.0.sale_date', '2026-08-28')
            ->assertJsonPath('report.table.rows.0.gross_sales', 100000)
            ->assertJsonPath('report.table.rows.0.returns_value', 20000)
            ->assertJsonPath('report.table.rows.0.net_sales', 80000)
            ->assertJsonPath('report.table.rows.0.net_hpp', 30000)
            ->assertJsonPath('report.table.rows.0.gross_profit', 50000)
            ->assertJsonPath('report.table.rows.0.gross_margin', 62.5);
        $this->getJson(route('laporan.penjualan.data', ['report' => 'shift', ...$query]))
            ->assertJsonPath('report.table.columns.4.empty_label', 'Waktu buka tidak tercatat')
            ->assertJsonPath('report.table.columns.5.empty_label', 'Shift masih aktif')
            ->assertJsonPath('report.table.columns.9.empty_label', 'Belum dihitung')
            ->assertJsonPath('report.table.columns.10.empty_label', 'Menunggu tutup shift');

        $categoryReport = $this->getJson(route('laporan.penjualan.data', ['report' => 'kategori', ...$query]))
            ->assertJsonPath('report.table.rows.0.category', 'Analgesik')
            ->assertJsonPath('report.table.rows.0.revenue', 100000);
        $this->assertSame(
            'Omzet sebelum retur',
            collect($categoryReport->json('report.table.columns'))->firstWhere('key', 'revenue')['label'],
        );
        $this->getJson(route('laporan.penjualan.data', ['report' => 'metode-bayar', ...$query]))
            ->assertJsonPath('report.table.rows.0.payment_method', 'qris')
            ->assertJsonPath('report.table.rows.0.amount', 100000);
        $this->getJson(route('laporan.penjualan.data', ['report' => 'retur', ...$query]))
            ->assertJsonPath('report.table.rows.0.return_number', 'RET-RPT-001')
            ->assertJsonPath('report.table.rows.0.transaction_number', 'SALE-RPT-001')
            ->assertJsonPath('report.table.rows.0.product_code', 'OBT-RPT-01')
            ->assertJsonPath('report.table.rows.0.item_reason', 'Kemasan rusak')
            ->assertJsonPath('report.table.rows.0.officer', 'Kasir Laporan')
            ->assertJsonPath('report.metrics.0.value', 20000);
        $this->getJson(route('laporan.penjualan.data', ['report' => 'diskon', ...$query]))
            ->assertJsonPath('report.table.rows.0.transaction_number', 'SALE-RPT-001')
            ->assertJsonPath('report.table.rows.0.customer', 'Pelanggan Laporan')
            ->assertJsonPath('report.table.rows.0.gross', 110000)
            ->assertJsonPath('report.table.rows.0.total_discount', 10000);
        $this->getJson(route('laporan.penjualan.data', ['report' => 'pembatalan', ...$query]))
            ->assertJsonPath('report.table.rows.0.transaction_number', 'SALE-CANCEL-001')
            ->assertJsonPath('report.table.rows.0.cancelled_at', '2026-08-28 11:05:00')
            ->assertJsonPath('report.table.rows.0.reason', 'Pasien membatalkan pesanan')
            ->assertJsonPath('report.table.rows.0.officer', 'Kasir Laporan')
            ->assertJsonPath('report.metrics.0.value', 75000);
        $this->getJson(route('laporan.penjualan.data', ['report' => 'jam', ...$query]))
            ->assertJsonPath('report.table.rows.0.sale_hour', 10);
    }

    public function test_sales_report_rejects_unavailable_branch_and_invalid_period(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'RPT-FOREIGN',
            'name' => 'Cabang Asing',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('laporan.penjualan.data', [
                'report' => 'ringkasan',
                'branch_id' => $foreignBranch->id,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');

        $this->getJson(route('laporan.penjualan.data', [
            'report' => 'ringkasan',
            'date_start' => '2026-08-28',
            'date_end' => '2026-08-01',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date_end');
    }
}
