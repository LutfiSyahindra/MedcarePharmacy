<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\Menu\Keuangan\FinanceAccountModel;
use App\Models\Menu\Keuangan\FinanceTransactionModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\Penjualan\CashierCashMovementModel;
use App\Models\Menu\Penjualan\CashierShiftModel;
use App\Models\Menu\Penjualan\PenjualanPaymentModel;
use App\Models\Menu\Penjualan\PenjualanTransactionBatchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Models\User;
use App\Support\SidebarPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 9, 14, 3, 0, 0, 'UTC'));
        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'FIN-01',
            'name' => 'Cabang Finance',
            'is_active' => true,
            'operational_timezone' => 'Asia/Jakarta',
        ]);
        $this->user->branches()->attach($this->branch->id);
        $this->user->givePermissionTo(SidebarPermissions::KEUANGAN);
        ApotekProfile::create([
            'branch_id' => $this->branch->id,
            'name' => 'Apotek Finance',
            'address' => 'Jl. Arus Kas No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'operational_hours' => $this->operationalHours(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_finance_ledger_combines_pos_return_cashier_and_manual_transactions(): void
    {
        $this->actingAs($this->user)
            ->get(route('keuangan.index'))
            ->assertOk()
            ->assertSee('Ringkasan Keuangan')
            ->assertSee('Pilih pekerjaan yang ingin dilakukan')
            ->assertDontSee('name="monthly_period"', false);

        $this->get(route('keuangan.monthly'))
            ->assertOk()
            ->assertSee('Omzet & keuntungan setiap bulan', false)
            ->assertSee('name="monthly_period"', false)
            ->assertDontSee('id="fnLedgerRows"', false);

        $this->get(route('keuangan.cash-flow'))
            ->assertOk()
            ->assertSee('Pendapatan vs pengeluaran')
            ->assertDontSee('id="fnMonthlyRows"', false);

        $this->get(route('keuangan.ledger'))
            ->assertOk()
            ->assertSee('Buku Kas Terpadu')
            ->assertSee('Catat transaksi')
            ->assertDontSee('id="fnCashFlowChart"', false);

        $this->get(route('keuangan.cashier'))
            ->assertOk()
            ->assertSee('Kasir yang sedang aktif')
            ->assertDontSee('id="fnLedgerRows"', false);

        $shift = $this->openShift();
        $sale = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'cashier_shift_id' => $shift->id,
            'nomor_transaksi' => 'POS-FIN-001',
            'tanggal_transaksi' => now(),
            'status' => 'completed',
            'payment_status' => 'paid',
            'grand_total' => 70000,
            'total_bayar' => 80000,
            'kembalian' => 10000,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => now(),
        ]);
        $detail = PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $sale->id,
            'kode_obat' => 'OBAT-FIN',
            'nama_obat' => 'Obat Finance',
            'qty_jual' => 1,
            'qty_stok' => 1,
            'harga_jual' => 70000,
            'subtotal_gross' => 70000,
            'subtotal_net' => 70000,
            'total_line' => 70000,
        ]);
        PenjualanTransactionBatchModel::create([
            'penjualan_transaction_detail_id' => $detail->id,
            'branch_id' => $this->branch->id,
            'no_batch' => 'BATCH-FIN',
            'qty_stok' => 1,
            'harga_beli' => 20000,
            'harga_jual' => 70000,
            'subtotal_gross' => 70000,
        ]);
        PenjualanPaymentModel::create([
            'penjualan_transaction_id' => $sale->id,
            'metode' => 'tunai',
            'amount' => 50000,
            'paid_at' => now(),
            'received_by' => $this->user->id,
        ]);
        PenjualanPaymentModel::create([
            'penjualan_transaction_id' => $sale->id,
            'metode' => 'qris',
            'amount' => 30000,
            'paid_at' => now(),
            'received_by' => $this->user->id,
        ]);
        CashierCashMovementModel::create([
            'cashier_shift_id' => $shift->id,
            'type' => 'cash_in',
            'amount' => 5000,
            'description' => 'Tambahan uang kecil',
            'created_by' => $this->user->id,
            'occurred_at' => now(),
        ]);
        ReturPenjualanModel::create([
            'branch_id' => $this->branch->id,
            'penjualan_transaction_id' => $sale->id,
            'nomor_retur' => 'RJ-FIN-001',
            'tanggal_retur' => today(),
            'status' => 'posted',
            'refund_method' => 'qris',
            'grand_total' => 10000,
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);

        $this->actingAs($this->user)->postJson(route('keuangan.transactions.store'), [
            'branch_id' => $this->branch->id,
            'type' => 'expense',
            'category' => 'operasional',
            'payment_method' => 'transfer',
            'amount' => 7000,
            'occurred_at' => '2026-09-14T10:00',
            'description' => 'Biaya internet',
        ])->assertCreated();

        $response = $this->actingAs($this->user)->getJson(route('keuangan.data', [
            'date_start' => '2026-09-14',
            'date_end' => '2026-09-14',
        ]));

        $response->assertOk()
            ->assertJsonPath('finance.summary.income', 75000)
            ->assertJsonPath('finance.summary.expense', 17000)
            ->assertJsonPath('finance.summary.net_cash_flow', 58000)
            ->assertJsonPath('finance.summary.pos_income', 70000)
            ->assertJsonPath('finance.summary.transaction_count', 5)
            ->assertJsonPath('finance.monthly_accounts.period', '2026-09')
            ->assertJsonPath('finance.monthly_accounts.accounts.0.code', 'SYS-OMZET-BULANAN')
            ->assertJsonPath('finance.monthly_accounts.accounts.0.amount', 60000)
            ->assertJsonPath('finance.monthly_accounts.accounts.1.code', 'SYS-KEUNTUNGAN-BULANAN')
            ->assertJsonPath('finance.monthly_accounts.accounts.1.amount', 40000)
            ->assertJsonPath('finance.monthly_accounts.accounts.2.code', 'SYS-LABA-BERSIH-BULANAN')
            ->assertJsonPath('finance.monthly_accounts.accounts.2.amount', 33000)
            ->assertJsonPath('finance.monthly_accounts.records.0.net_revenue', 60000)
            ->assertJsonPath('finance.monthly_accounts.records.0.net_hpp', 20000)
            ->assertJsonPath('finance.monthly_accounts.records.0.gross_profit', 40000)
            ->assertJsonPath('finance.monthly_accounts.records.0.operating_expenses', 7000)
            ->assertJsonPath('finance.monthly_accounts.records.0.net_profit', 33000)
            ->assertJsonPath('finance.monthly_accounts.records.0.net_margin', 55)
            ->assertJsonCount(12, 'finance.monthly_accounts.records')
            ->assertJsonCount(5, 'finance.rows');

        $this->assertSame(3, FinanceAccountModel::query()->where('branch_id', $this->branch->id)->count());
        $this->assertDatabaseHas('finance_accounts', [
            'branch_id' => $this->branch->id,
            'code' => 'SYS-OMZET-BULANAN',
            'type' => 'reporting',
            'is_active' => true,
        ]);

        $this->assertEqualsCanonicalizing(
            ['cashier', 'manual', 'pos', 'pos', 'return'],
            collect($response->json('finance.rows'))->pluck('source')->all(),
        );
    }

    public function test_stock_purchase_is_not_double_counted_as_an_operating_expense(): void
    {
        $this->actingAs($this->user)->postJson(route('keuangan.transactions.store'), [
            'branch_id' => $this->branch->id,
            'type' => 'expense',
            'category' => 'pembelian_stok',
            'payment_method' => 'transfer',
            'amount' => 25000,
            'occurred_at' => '2026-09-14T10:00',
            'description' => 'Pembelian persediaan',
        ])->assertCreated();

        $this->actingAs($this->user)->getJson(route('keuangan.data', [
            'date_start' => '2026-09-01',
            'date_end' => '2026-09-30',
        ]))->assertOk()
            ->assertJsonPath('finance.summary.expense', 25000)
            ->assertJsonPath('finance.monthly_accounts.records.0.operating_expenses', 0)
            ->assertJsonPath('finance.monthly_accounts.records.0.net_profit', 0);

        $this->assertDatabaseHas('finance_categories', [
            'branch_id' => $this->branch->id,
            'code' => 'MANUAL-PEMBELIAN-STOK',
            'group' => 'Persediaan',
            'is_operational' => false,
        ]);
    }

    public function test_monthly_accounts_can_be_filtered_independently_to_a_specific_month(): void
    {
        $this->actingAs($this->user)->postJson(route('keuangan.transactions.store'), [
            'branch_id' => $this->branch->id,
            'type' => 'expense',
            'category' => 'operasional',
            'payment_method' => 'transfer',
            'amount' => 8000,
            'occurred_at' => '2026-08-20T10:00',
            'description' => 'Biaya operasional Agustus',
        ])->assertCreated();

        $this->actingAs($this->user)->postJson(route('keuangan.transactions.store'), [
            'branch_id' => $this->branch->id,
            'type' => 'expense',
            'category' => 'operasional',
            'payment_method' => 'transfer',
            'amount' => 9000,
            'occurred_at' => '2026-09-10T10:00',
            'description' => 'Biaya operasional September',
        ])->assertCreated();

        $this->actingAs($this->user)->getJson(route('keuangan.data', [
            'date_start' => '2026-09-01',
            'date_end' => '2026-09-14',
            'monthly_period' => '2026-08',
        ]))->assertOk()
            ->assertJsonPath('finance.monthly_accounts.period', '2026-08')
            ->assertJsonPath('finance.monthly_accounts.filter_applied', true)
            ->assertJsonPath('finance.monthly_accounts.accounts.2.amount', -8000)
            ->assertJsonPath('finance.monthly_accounts.records.0.period', '2026-08')
            ->assertJsonPath('finance.monthly_accounts.records.0.operating_expenses', 8000)
            ->assertJsonCount(1, 'finance.monthly_accounts.records');
    }

    public function test_monthly_account_filter_rejects_invalid_and_future_months(): void
    {
        $this->actingAs($this->user)->getJson(route('keuangan.data', [
            'monthly_period' => '2026-08-01',
        ]))->assertUnprocessable()->assertJsonValidationErrors('monthly_period');

        $this->actingAs($this->user)->getJson(route('keuangan.data', [
            'monthly_period' => '2026-10',
        ]))->assertUnprocessable()->assertJsonValidationErrors('monthly_period');
    }

    public function test_cash_manual_transaction_updates_and_can_reverse_the_active_drawer(): void
    {
        $shift = $this->openShift(100000);

        $create = $this->actingAs($this->user)->postJson(route('keuangan.transactions.store'), [
            'branch_id' => $this->branch->id,
            'type' => 'expense',
            'category' => 'operasional',
            'payment_method' => 'tunai',
            'amount' => 30000,
            'occurred_at' => '2026-09-14T08:00',
            'reference_no' => 'BKK-001',
            'description' => 'Biaya pengiriman',
        ])->assertCreated()
            ->assertJsonPath('message', 'Transaksi tersimpan dan kas shift aktif telah diperbarui.');

        $transaction = FinanceTransactionModel::firstOrFail();
        $this->assertStringStartsWith('KU-20260914-FIN01-', $create->json('transaction_number'));
        $this->assertDatabaseHas('cashier_cash_movements', [
            'cashier_shift_id' => $shift->id,
            'finance_transaction_id' => $transaction->id,
            'type' => 'cash_out',
            'amount' => 30000,
        ]);

        $this->actingAs($this->user)->putJson(route('keuangan.transactions.void', $transaction->id), [
            'reason' => 'Salah memilih transaksi',
        ])->assertOk();

        $this->assertDatabaseHas('finance_transactions', [
            'id' => $transaction->id,
            'status' => 'voided',
            'void_reason' => 'Salah memilih transaksi',
        ]);
        $this->assertDatabaseCount('cashier_cash_movements', 2);
        $this->assertDatabaseHas('cashier_cash_movements', [
            'finance_transaction_id' => $transaction->id,
            'type' => 'cash_in',
            'amount' => 30000,
        ]);

        $this->actingAs($this->user)->getJson(route('keuangan.data', [
            'date_start' => '2026-09-14',
            'date_end' => '2026-09-14',
        ]))->assertOk()
            ->assertJsonPath('finance.summary.income', 0)
            ->assertJsonPath('finance.summary.expense', 0)
            ->assertJsonPath('finance.monthly_accounts.records.0.operating_expenses', 0)
            ->assertJsonPath('finance.monthly_accounts.records.0.net_profit', 0)
            ->assertJsonPath('finance.open_drawers.expected_cash', 100000)
            ->assertJsonPath('finance.rows.0.status', 'voided');
    }

    public function test_cash_entry_requires_the_users_active_shift(): void
    {
        $this->actingAs($this->user)->postJson(route('keuangan.transactions.store'), [
            'branch_id' => $this->branch->id,
            'type' => 'income',
            'category' => 'pendapatan_lain',
            'payment_method' => 'tunai',
            'amount' => 25000,
            'occurred_at' => '2026-09-14T10:00',
            'description' => 'Pendapatan layanan',
        ])->assertUnprocessable()->assertJsonValidationErrors('cashier_shift');

        $this->assertDatabaseCount('finance_transactions', 0);
    }

    public function test_supplier_payables_and_customer_receivables_are_settled_through_finance(): void
    {
        $supplier = DistributorModel::create([
            'kode' => 'SUP-FIN-01',
            'nama' => 'Supplier Finance',
            'is_active' => true,
        ]);
        $purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-FIN-001',
            'distributor_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'tanggal_po' => today(),
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);
        $receipt = PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-FIN-001',
            'purchase_order_id' => $purchaseOrder->id,
            'distributor_id' => $supplier->id,
            'nomor_faktur' => 'INV-FIN-001',
            'tanggal_penerimaan' => today(),
            'tanggal_faktur' => today(),
            'tanggal_jatuh_tempo' => today()->subDay(),
            'grand_total' => 100000,
            'total_faktur' => 100000,
            'jumlah_dibayar' => 0,
            'sisa_hutang' => 100000,
            'status_pembayaran' => 'belum_dibayar',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);
        $sale = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'POS-CREDIT-001',
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => 'penjualan_kredit',
            'status' => 'completed',
            'payment_status' => 'credit',
            'customer_name' => 'Pelanggan Kredit',
            'grand_total' => 80000,
            'total_bayar' => 0,
            'sisa_tagihan' => 80000,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->get(route('keuangan.obligations'))
            ->assertOk()
            ->assertSee('Hutang Supplier')
            ->assertSee('Piutang Pelanggan & Instansi', false);

        $this->getJson(route('keuangan.obligations.data'))
            ->assertOk()
            ->assertJsonPath('obligations.summary.payable', 100000)
            ->assertJsonPath('obligations.summary.receivable', 80000)
            ->assertJsonPath('obligations.summary.overdue_count', 1)
            ->assertJsonPath('obligations.payables.0.counterparty', 'Supplier Finance')
            ->assertJsonPath('obligations.receivables.0.counterparty', 'Pelanggan Kredit');

        $payableResponse = $this->postJson(route('keuangan.payables.pay', $receipt->id), [
            'payment_method' => 'transfer',
            'amount' => 40000,
            'occurred_at' => '2026-09-14T10:00',
            'reference_no' => 'TRF-SUP-001',
        ])->assertCreated();
        $receivableResponse = $this->postJson(route('keuangan.receivables.collect', $sale->id), [
            'payment_method' => 'qris',
            'amount' => 30000,
            'occurred_at' => '2026-09-14T11:00',
            'reference_no' => 'QR-AR-001',
        ])->assertCreated();

        $receipt->refresh();
        $sale->refresh();
        $this->assertEquals(40000, (float) $receipt->jumlah_dibayar);
        $this->assertEquals(60000, (float) $receipt->sisa_hutang);
        $this->assertSame('sebagian', $receipt->status_pembayaran);
        $this->assertEquals(30000, (float) $sale->total_bayar);
        $this->assertEquals(50000, (float) $sale->sisa_tagihan);
        $this->assertSame('credit', $sale->payment_status);

        $supplierJournal = FinanceTransactionModel::where('number', $payableResponse->json('transaction_number'))->firstOrFail();
        $receivableJournal = FinanceTransactionModel::where('number', $receivableResponse->json('transaction_number'))->firstOrFail();
        $this->assertSame('supplier_payable', $supplierJournal->source_type);
        $this->assertSame('customer_receivable', $receivableJournal->source_type);
        $this->assertDatabaseHas('penjualan_payments', [
            'penjualan_transaction_id' => $sale->id,
            'finance_transaction_id' => $receivableJournal->id,
            'amount' => 30000,
        ]);

        $this->getJson(route('keuangan.data', ['date_start' => '2026-09-14', 'date_end' => '2026-09-14']))
            ->assertOk()
            ->assertJsonPath('finance.summary.income', 30000)
            ->assertJsonPath('finance.summary.expense', 40000)
            ->assertJsonPath('finance.summary.transaction_count', 2);

        foreach ([$supplierJournal, $receivableJournal] as $journal) {
            $this->putJson(route('keuangan.transactions.void', $journal->id), [
                'reason' => 'Koreksi pembayaran pengujian',
            ])->assertOk();
        }

        $this->assertEquals(0, (float) $receipt->fresh()->jumlah_dibayar);
        $this->assertEquals(100000, (float) $receipt->fresh()->sisa_hutang);
        $this->assertEquals(0, (float) $sale->fresh()->total_bayar);
        $this->assertEquals(80000, (float) $sale->fresh()->sisa_tagihan);
        $this->assertDatabaseMissing('penjualan_payments', ['finance_transaction_id' => $receivableJournal->id]);
    }

    public function test_user_cannot_post_finance_transaction_to_an_unassigned_branch(): void
    {
        $otherBranch = BranchModel::create([
            'code' => 'FIN-02',
            'name' => 'Cabang Lain',
            'is_active' => true,
            'operational_timezone' => 'Asia/Jakarta',
        ]);

        $this->actingAs($this->user)->postJson(route('keuangan.transactions.store'), [
            'branch_id' => $otherBranch->id,
            'type' => 'expense',
            'category' => 'operasional',
            'payment_method' => 'transfer',
            'amount' => 10000,
            'occurred_at' => '2026-09-14T10:00',
            'description' => 'Tidak boleh masuk',
        ])->assertUnprocessable()->assertJsonValidationErrors('branch_id');
    }

    public function test_finance_routes_require_the_finance_permission(): void
    {
        $unauthorized = User::factory()->create();
        $unauthorized->branches()->attach($this->branch->id);

        foreach (['keuangan.index', 'keuangan.monthly', 'keuangan.cash-flow', 'keuangan.obligations', 'keuangan.ledger', 'keuangan.cashier'] as $route) {
            $this->actingAs($unauthorized)->get(route($route))->assertForbidden();
        }

        $this->actingAs($unauthorized)->getJson(route('keuangan.data'))->assertForbidden();
    }

    private function openShift(float $openingAmount = 100000): CashierShiftModel
    {
        return CashierShiftModel::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'shift_number' => 'KS-FIN-'.str_pad((string) (CashierShiftModel::count() + 1), 4, '0', STR_PAD_LEFT),
            'status' => 'open',
            'opening_amount' => $openingAmount,
            'expected_cash' => $openingAmount,
            'opened_at' => now(),
        ]);
    }

    private function operationalHours(): array
    {
        return collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ->mapWithKeys(fn (string $day) => [$day => [
                'enabled' => true,
                'open' => '08:00',
                'close' => '20:00',
            ]])->all();
    }
}
