<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PenerimaanPurchaseOrderNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PembelianModel $purchaseOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-PO-NAV',
            'name' => 'Cabang Navigasi PO',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($branch->id);
        $supplier = DistributorModel::create([
            'kode' => 'DST-PO-NAV',
            'nama' => 'Supplier Navigasi PO',
            'is_active' => true,
        ]);
        $this->purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-NAV-001',
            'distributor_id' => $supplier->id,
            'branch_id' => $branch->id,
            'tanggal_po' => '2026-08-01',
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);
        $this->actingAs($this->user);
    }

    public function test_purchase_order_table_reports_receipt_availability_for_status_navigation(): void
    {
        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $this->purchaseOrder->id)
            ->assertJsonPath('data.0.has_receipt', false);

        $receipt = $this->createReceipt($this->purchaseOrder, 'draft', '2026-08-01');
        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.has_receipt', true)
            ->assertJsonPath('data.0.status', 'dalam_penerimaan');

        $receipt->update(['status' => 'cancelled']);
        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.has_receipt', true);

        $receipt->delete();
        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.has_receipt', false);

        $this->createReceipt($this->purchaseOrder, 'posted', '2026-08-01');
        $otherOrder = $this->purchaseOrder->replicate();
        $otherOrder->no_po = 'PO-NAV-002';
        $otherOrder->save();
        $response = $this->getJson(route('pembelian.table'))->assertOk();
        $rows = collect($response->json('data'))->keyBy('id');
        $this->assertTrue($rows[$this->purchaseOrder->id]['has_receipt']);
        $this->assertFalse($rows[$otherOrder->id]['has_receipt']);
    }

    public function test_navigation_opens_latest_draft_even_when_posted_and_cancelled_receipts_are_newer(): void
    {
        $this->createReceipt($this->purchaseOrder, 'draft', '2026-08-01');
        $draft = $this->createReceipt($this->purchaseOrder, 'draft', '2026-08-02');
        $this->createReceipt($this->purchaseOrder, 'posted', '2026-09-01');
        $this->createReceipt($this->purchaseOrder, 'cancelled', '2026-10-01');

        $this->get(route('penerimaan.penerimaan', ['purchase_order_id' => $this->purchaseOrder->id]))
            ->assertOk()
            ->assertViewHas('selectedPurchaseOrder', fn ($po) => $po->is($this->purchaseOrder))
            ->assertViewHas('initialReceiptId', $draft->id)
            ->assertSee('PO-NAV-001')
            ->assertSee('termasuk draft yang belum diposting.')
            ->assertSee('Tampilkan Semua PO');

        $this->getJson(route('penerimaan.show', $draft->id))
            ->assertOk()
            ->assertJsonPath('header.id', $draft->id)
            ->assertJsonPath('header.status', 'draft')
            ->assertJsonPath('header.purchase_order.id', $this->purchaseOrder->id);
        $this->assertSame('draft', $draft->fresh()->status);
    }

    public function test_navigation_opens_latest_posted_receipt_for_completed_purchase_order(): void
    {
        // The receiving-status migration only extends the PO enum on MySQL.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }
        $this->purchaseOrder->update(['status' => 'selesai']);
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = OFF');
        }
        $this->createReceipt($this->purchaseOrder, 'posted', '2026-08-01');
        $receipt = $this->createReceipt($this->purchaseOrder, 'posted', '2026-08-02');
        $this->createReceipt($this->purchaseOrder, 'cancelled', '2026-09-01');

        $this->get(route('penerimaan.penerimaan', ['purchase_order_id' => $this->purchaseOrder->id]))
            ->assertOk()
            ->assertViewHas('initialReceiptId', $receipt->id);
        $this->getJson(route('penerimaan.show', $receipt->id))
            ->assertOk()
            ->assertJsonPath('header.status', 'posted');
    }

    public function test_receipt_table_filters_by_exact_purchase_order_and_includes_unposted_and_old_receipts(): void
    {
        $draft = $this->createReceipt($this->purchaseOrder, 'draft', '2026-08-01');
        $posted = $this->createReceipt($this->purchaseOrder, 'posted', '2026-09-01');
        $cancelled = $this->createReceipt($this->purchaseOrder, 'cancelled', '2026-10-01');
        $otherOrder = $this->purchaseOrder->replicate();
        $otherOrder->no_po = 'PO-NAV-0010';
        $otherOrder->save();
        $this->createReceipt($otherOrder, 'draft', '2026-10-01');

        $response = $this->getJson(route('penerimaan.table', [
            'purchase_order_id' => $this->purchaseOrder->id,
            'date_start' => '',
            'date_end' => '',
        ]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('summary.total', 3)
            ->assertJsonPath('summary.draft', 1)
            ->assertJsonPath('summary.posted', 1);
        $this->assertEqualsCanonicalizing(
            [$draft->id, $posted->id, $cancelled->id],
            array_column($response->json('data'), 'id')
        );

        $this->getJson(route('penerimaan.table', [
            'purchase_order_id' => $this->purchaseOrder->id,
            'date_start' => '2026-09-01',
            'date_end' => '2026-09-30',
        ]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.id', $posted->id);
    }

    public function test_navigation_without_receipts_or_without_purchase_order_does_not_open_a_receipt(): void
    {
        $this->get(route('penerimaan.penerimaan', ['purchase_order_id' => $this->purchaseOrder->id]))
            ->assertOk()
            ->assertViewHas('selectedPurchaseOrder', fn ($po) => $po->is($this->purchaseOrder))
            ->assertViewHas('initialReceiptId', null);
        $this->createReceipt($this->purchaseOrder, 'cancelled', '2026-08-01');
        $this->get(route('penerimaan.penerimaan', ['purchase_order_id' => $this->purchaseOrder->id]))
            ->assertOk()
            ->assertViewHas('initialReceiptId', null);
        $this->get(route('penerimaan.penerimaan'))
            ->assertOk()
            ->assertViewHas('selectedPurchaseOrder', null)
            ->assertViewHas('initialReceiptId', null)
            ->assertDontSee('Tampilkan Semua PO');
    }

    public function test_purchase_order_navigation_and_filter_respect_branch_access(): void
    {
        $otherBranch = BranchModel::create([
            'code' => 'CB-PO-NAV-OTHER',
            'name' => 'Cabang Lain',
            'is_active' => true,
        ]);
        $this->purchaseOrder->update(['branch_id' => $otherBranch->id]);
        $receipt = $this->createReceipt($this->purchaseOrder, 'draft', '2026-08-01');

        $this->get(route('penerimaan.penerimaan', ['purchase_order_id' => $this->purchaseOrder->id]))
            ->assertNotFound();
        $this->getJson(route('penerimaan.table', ['purchase_order_id' => $this->purchaseOrder->id]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 0);
        $this->getJson(route('penerimaan.show', $receipt->id))->assertNotFound();
    }

    public function test_invalid_purchase_order_identifiers_are_rejected(): void
    {
        foreach (['invalid', 0, -1, [$this->purchaseOrder->id]] as $id) {
            $this->getJson(route('penerimaan.penerimaan', ['purchase_order_id' => $id]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('purchase_order_id');
            $this->getJson(route('penerimaan.table', ['purchase_order_id' => $id]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('purchase_order_id');
        }
        $this->get(route('penerimaan.penerimaan', ['purchase_order_id' => 999999]))->assertNotFound();
    }

    private function createReceipt(PembelianModel $po, string $status, string $date): PenerimaanBarangModel
    {
        $number = 'PB-NAV-'.(PenerimaanBarangModel::max('id') + 1);

        return PenerimaanBarangModel::create([
            'nomor_penerimaan' => $number,
            'purchase_order_id' => $po->id,
            'distributor_id' => $po->distributor_id,
            'nomor_faktur' => 'INV-'.$number,
            'tanggal_penerimaan' => $date,
            'status' => $status,
            'created_by' => $this->user->id,
        ]);
    }
}
