<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\SatuansModel;
use App\Models\User;
use App\Services\Notifikasi\TransactionNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderWorkflowIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private array $payload;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->user = User::factory()->create();
        $branch = BranchModel::create(['code' => 'PO-INTEGRITY', 'name' => 'Cabang PO', 'is_active' => true]);
        $this->user->branches()->attach($branch->id);
        $unit = SatuansModel::create(['kode' => 'PCS-INTEGRITY', 'nama' => 'PCS', 'is_active' => true]);
        $distributor = DistributorModel::create(['kode' => 'DST-INTEGRITY', 'nama' => 'Distributor PO', 'is_active' => true]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-INTEGRITY', 'nama_obat' => 'Obat PO', 'satuan_id' => $unit->id, 'is_active' => true,
        ]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id, 'satuan_id' => $unit->id, 'konversi' => 1, 'is_default' => true,
        ]);
        $this->payload = [
            'distributor_id' => $distributor->id, 'tanggal' => '22-10-2026', 'total_estimasi' => 0,
            'obat_id' => [$medicine->id], 'qty' => [2.5], 'harga_estimasi' => [1250.5],
            'diskon_1' => [0], 'diskon_2' => [0], 'diskon_3' => [0], 'ppn' => [11],
            'subtotal' => [0], 'satuan_id' => [$conversion->id], 'save_as_draft' => true,
        ];
        $this->actingAs($this->user);
    }

    public function test_fractional_quantities_use_a_decimal_column_and_reconcile_with_the_po_total(): void
    {
        $column = collect(Schema::getColumns('purchase_order_details'))->firstWhere('name', 'qty');
        $this->assertContains($column['type_name'], ['numeric', 'decimal']);
        $order = $this->saveOrder();
        $detail = $order->details()->firstOrFail();
        $this->assertSame('2.50', $detail->qty);
        $this->assertSame('3470.14', $detail->subtotal);
        $this->assertSame($detail->subtotal, $order->total_estimasi);
        $this->assertTrue($order->is($detail->pembelian));
    }

    #[DataProvider('receivingHistory')]
    public function test_receiving_history_blocks_reopening_editing_and_deleting(string $receiptStatus): void
    {
        $order = $this->saveOrder();
        $order->update(['status' => 'approved', 'approved_by' => $this->user->id]);
        $receipt = PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-INTEGRITY', 'purchase_order_id' => $order->id,
            'distributor_id' => $order->distributor_id, 'nomor_faktur' => 'INV-INTEGRITY',
            'tanggal_penerimaan' => '2026-10-22', 'status' => $receiptStatus, 'created_by' => $this->user->id,
        ]);
        $this->makeApprover();
        $detailIds = $order->details()->pluck('id')->all();

        $this->getJson(route('pembelian.edit', $order->id))->assertUnprocessable();
        $this->putJson(route('pembelian.update', $order->id), $this->payload)->assertUnprocessable();
        $this->putJson(route('pembelian.reopenApproval', $order->id))->assertUnprocessable();
        $this->deleteJson(route('pembelian.destroy', $order->id))->assertUnprocessable();
        $this->assertSame('approved', $order->fresh()->status);
        $this->assertSame($detailIds, $order->details()->pluck('id')->all());
        $this->assertNotNull($receipt->fresh());

        $actions = $this->getJson(route('pembelian.table'))->assertOk()->json('data.0.actions');
        foreach (['editPembelian(', 'reopenPembelian(', 'deletePembelian('] as $action) {
            $this->assertStringNotContainsString($action, $actions);
        }
    }

    public static function receivingHistory(): array
    {
        return [['draft'], ['posted'], ['cancelled']];
    }

    #[DataProvider('receivedStatuses')]
    public function test_received_orders_cannot_return_to_approval_or_change_details(string $status): void
    {
        $order = $this->saveOrder();
        // The original receiving-status migration extends the enum only on MySQL.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }
        try {
            $order->update(['status' => $status]);
        } finally {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA ignore_check_constraints = OFF');
            }
        }
        $this->makeApprover();
        foreach (['pembelian.approve', 'pembelian.reject', 'pembelian.reopenApproval'] as $route) {
            $this->putJson(route($route, $order->id))->assertUnprocessable();
        }
        $this->getJson(route('pembelian.edit', $order->id))->assertUnprocessable();
        $this->putJson(route('pembelian.update', $order->id), $this->payload)->assertUnprocessable();
        $this->deleteJson(route('pembelian.destroy', $order->id))->assertUnprocessable();
        $this->assertSame($status, $order->fresh()->status);
        $this->assertSame(1, $order->details()->count());
    }

    public static function receivedStatuses(): array
    {
        return [['diterima_sebagian'], ['selesai']];
    }

    public function test_approved_po_without_receipts_must_be_reopened_before_edit_or_delete(): void
    {
        $order = $this->saveOrder();
        $order->update(['status' => 'approved', 'approved_by' => $this->user->id]);
        $this->makeApprover();
        $this->deleteJson(route('pembelian.destroy', $order->id))->assertUnprocessable();
        $this->putJson(route('pembelian.reopenApproval', $order->id))->assertOk();
        $this->getJson(route('pembelian.edit', $order->id))->assertOk();
        $this->assertNull($order->fresh()->approved_by);
        $this->deleteJson(route('pembelian.destroy', $order->id))->assertOk();
        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('purchase_order_details', 0);
    }

    public function test_notification_failure_does_not_report_a_committed_approval_as_failed(): void
    {
        $order = $this->saveOrder();
        $order->update(['status' => 'waiting_approval']);
        $this->makeApprover();
        $this->partialMock(TransactionNotificationService::class, function ($mock) {
            $mock->shouldReceive('isApprovalRole')->andReturn(true);
            $mock->shouldReceive('notifyActionResult')->once()->andThrow(new \RuntimeException('Notification unavailable'));
        });
        $this->putJson(route('pembelian.approve', $order->id))->assertOk()->assertJsonPath('status', 'success');
        $this->assertSame('approved', $order->fresh()->status);
    }

    public function test_malformed_or_misaligned_rows_fail_validation_without_creating_orders(): void
    {
        foreach ([
            ['qty' => [1 => 2.5]],
            ['obat_id' => [['invalid']]],
            ['satuan_id' => [['invalid']]],
            ['qty' => [2.555]],
            ['harga_estimasi' => [1250.555]],
            ['obat_id' => [$this->payload['obat_id'][0], $this->payload['obat_id'][0]]],
            ['obat_id' => [999999]],
            ['no_po' => ['invalid']],
        ] as $invalid) {
            $this->postJson(route('pembelian.store'), [...$this->payload, ...$invalid])->assertUnprocessable();
        }
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_computed_subtotals_and_totals_cannot_overflow_money_columns(): void
    {
        $this->postJson(route('pembelian.store'), [...$this->payload, 'qty' => [100], 'harga_estimasi' => [9999999999999]])
            ->assertUnprocessable()->assertJsonValidationErrors('harga_estimasi.0');
        $this->postJson(route('pembelian.store'), [...$this->payload, 'biaya_asuransi' => '9999999999000'])
            ->assertUnprocessable()->assertJsonValidationErrors('total_estimasi');
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_date_filters_validate_ranges_and_include_both_boundaries(): void
    {
        $this->saveOrder()->update(['tanggal_po' => '2026-10-21']);
        $this->saveOrder();
        $this->saveOrder()->update(['tanggal_po' => '2026-10-23']);
        $this->getJson(route('pembelian.table', ['date_start' => '2026-10-21', 'date_end' => '2026-10-22']))
            ->assertOk()->assertJsonPath('summary.total', 2)->assertJsonCount(2, 'data');
        $this->getJson(route('pembelian.table', ['date_end' => '2026-10-21']))
            ->assertOk()->assertJsonPath('summary.total', 1);
        $this->getJson(route('pembelian.table', ['date_start' => '2026-10-23']))
            ->assertOk()->assertJsonPath('summary.total', 1);
        foreach ([['date_start' => 'invalid'], ['date_start' => '2026-10-23', 'date_end' => '2026-10-22']] as $invalid) {
            $this->getJson(route('pembelian.table', $invalid))->assertUnprocessable();
        }
    }

    private function saveOrder(): PembelianModel
    {
        $id = $this->postJson(route('pembelian.store'), $this->payload)->assertOk()->json('data.id');

        return PembelianModel::findOrFail($id);
    }

    private function makeApprover(): void
    {
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $approver = User::factory()->create();
        $approver->assignRole('Admin');
        $approver->branches()->attach($this->user->branches()->pluck('branches.id'));
        $this->actingAs($approver);
    }
}
