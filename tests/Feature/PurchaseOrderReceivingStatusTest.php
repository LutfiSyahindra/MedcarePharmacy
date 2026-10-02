<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderReceivingStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PembelianModel $purchaseOrder;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-PO-RECEIVING',
            'name' => 'Cabang PO Receiving',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($branch->id);
        $distributor = DistributorModel::create([
            'kode' => 'DST-PO-RECEIVING',
            'nama' => 'Distributor PO Receiving',
            'is_active' => true,
        ]);
        $this->purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-RECEIVING-001',
            'distributor_id' => $distributor->id,
            'branch_id' => $branch->id,
            'tanggal_po' => '2026-10-03',
            'status' => 'approved',
            'created_by' => $this->user->id,
            'approved_by' => $this->user->id,
        ]);
        $this->actingAs($this->user);
    }

    public function test_table_status_summary_and_filter_follow_draft_receipts(): void
    {
        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('summary.approved', 1)
            ->assertJsonPath('summary.dalam_penerimaan', 0);

        $this->createReceipt('posted');
        $this->createReceipt('cancelled');

        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('summary.dalam_penerimaan', 0);

        $this->createReceipt('draft');

        $response = $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.status', 'dalam_penerimaan')
            ->assertJsonPath('data.0.purchase_order_status', 'approved')
            ->assertJsonPath('summary.approved', 0)
            ->assertJsonPath('summary.dalam_penerimaan', 1);

        $this->assertStringNotContainsString('editPembelian(', $response->json('data.0.actions'));
        $this->assertSame('approved', $this->purchaseOrder->fresh()->status);

        $this->getJson(route('pembelian.table', [
            'columns' => [['data' => 'status', 'name' => 'status', 'searchable' => 'true',
                'search' => ['value' => 'dalam_penerimaan', 'regex' => 'false']]],
        ]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $this->purchaseOrder->id);

        $this->getJson(route('pembelian.table', [
            'columns' => [['data' => 'status', 'name' => 'status', 'searchable' => 'true',
                'search' => ['value' => 'approved', 'regex' => 'false']]],
        ]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 0);
    }

    public function test_receiving_status_clears_only_after_all_drafts_are_cancelled_or_deleted(): void
    {
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->user->assignRole('Admin');
        $firstDraft = $this->createReceipt('draft');
        $secondDraft = $this->createReceipt('draft');

        $this->deleteJson(route('penerimaan.destroy', $firstDraft->id))->assertOk();
        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.status', 'dalam_penerimaan')
            ->assertJsonPath('summary.dalam_penerimaan', 1);

        $this->putJson(route('penerimaan.cancel', $secondDraft->id))->assertOk();
        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('summary.dalam_penerimaan', 0);

        $lastDraft = $this->createReceipt('draft');
        $this->deleteJson(route('penerimaan.destroy', $lastDraft->id))->assertOk();
        $this->getJson(route('pembelian.table'))
            ->assertOk()
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('summary.dalam_penerimaan', 0);
    }

    private function createReceipt(string $status): PenerimaanBarangModel
    {
        $number = 'PB-RECEIVING-'.(PenerimaanBarangModel::max('id') + 1);

        return PenerimaanBarangModel::create([
            'nomor_penerimaan' => $number,
            'purchase_order_id' => $this->purchaseOrder->id,
            'distributor_id' => $this->purchaseOrder->distributor_id,
            'nomor_faktur' => 'INV-'.$number,
            'tanggal_penerimaan' => '2026-10-03',
            'status' => $status,
            'created_by' => $this->user->id,
        ]);
    }
}
