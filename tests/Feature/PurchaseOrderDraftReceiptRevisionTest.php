<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderDraftReceiptRevisionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PembelianModel $order;

    private SatuansModel $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']));
        $branch = BranchModel::create(['code' => 'REV', 'name' => 'Cabang Revisi', 'is_active' => true]);
        $this->user->branches()->attach($branch->id);
        $supplier = DistributorModel::create(['kode' => 'REV', 'nama' => 'Supplier Revisi', 'is_active' => true]);
        $this->unit = SatuansModel::create(['kode' => 'REV', 'nama' => 'PCS', 'is_active' => true]);
        $this->order = PembelianModel::create([
            'no_po' => 'PO-REV', 'distributor_id' => $supplier->id, 'branch_id' => $branch->id,
            'tanggal_po' => '2026-10-22', 'status' => 'approved',
            'created_by' => $this->user->id, 'approved_by' => $this->user->id,
        ]);
        $this->addMedicine('A');
        $this->actingAs($this->user);
    }

    public function test_draft_batches_survive_revision_and_require_reapproval_and_review_before_posting(): void
    {
        $receipt = $this->saveReceipt([1, 1]);
        $otherReceipt = $this->saveReceipt([3]);
        $detailId = $this->order->details()->firstOrFail()->id;
        $batchIds = $receipt->details()->pluck('id')->all();
        $payload = $this->receiptPayload([1, 1]);

        $actions = $this->getJson(route('pembelian.table'))->assertOk()->json('data.0.actions');
        $this->assertStringContainsString('reopenPembelian(', $actions);
        $this->getJson(route('pembelian.edit', $this->order->id))->assertUnprocessable();
        $this->putJson(route('pembelian.reopenApproval', $this->order->id))->assertOk();
        $this->assertNull($this->order->fresh()->approved_by);
        $this->deleteJson(route('pembelian.destroy', $this->order->id))->assertUnprocessable();
        $this->getJson(route('pembelian.table'))->assertOk()
            ->assertJsonPath('data.0.status', 'waiting_approval')
            ->assertJsonPath('summary.waiting_approval', 1);

        $changes = $this->orderPayload();
        $changes['qty'] = [12];
        $changes['biaya_pengiriman'] = 120;
        $this->putJson(route('pembelian.update', $this->order->id), $changes)->assertOk();
        $this->assertSame($detailId, $this->order->details()->firstOrFail()->id);
        $this->assertSame($batchIds, $receipt->details()->pluck('id')->all());
        $this->assertSame('BATCH-0', $receipt->details()->firstOrFail()->no_batch);
        $this->assertTrue($receipt->fresh()->requires_po_review);
        $this->assertTrue($otherReceipt->fresh()->requires_po_review);
        $this->putJson(route('penerimaan.post', $receipt->id), ['diskon_untuk' => 'pasien'])
            ->assertUnprocessable()->assertJsonValidationErrors('purchase_order_id');
        $this->assertDatabaseCount('stok_batches', 0);

        $this->putJson(route('pembelian.approve', $this->order->id))->assertOk();
        $this->getJson(route('penerimaan.edit', $receipt->id))->assertOk()
            ->assertJsonPath('header.requires_po_review', true)
            ->assertJsonPath('po_payload.revision', 1)
            ->assertJsonPath('po_payload.details.0.qty_po', 12);
        $this->putJson(route('penerimaan.post', $receipt->id), ['diskon_untuk' => 'pasien'])
            ->assertUnprocessable()->assertJsonValidationErrors('purchase_order_id');
        $this->putJson(route('penerimaan.update', $receipt->id), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('purchase_order_revision');
        $this->assertTrue($receipt->fresh()->requires_po_review);

        $payload['purchase_order_revision'] = 1;
        $this->putJson(route('penerimaan.update', $receipt->id), $payload)->assertOk();
        $this->assertFalse($receipt->fresh()->requires_po_review);
        $this->assertEquals(20, $receipt->fresh()->biaya_lain);
        $this->assertEquals(220, $receipt->fresh()->total_faktur);
        $this->assertSame(['BATCH-0', 'BATCH-1'], $receipt->details()->pluck('no_batch')->all());
        $this->assertEquals(12, $receipt->details()->firstOrFail()->qty_po);
        $this->assertTrue($otherReceipt->fresh()->requires_po_review);
        $this->assertStringNotContainsString('postPenerimaan(', collect($this->getJson(route('penerimaan.table'))
            ->assertOk()->json('data'))->firstWhere('id', $otherReceipt->id)['actions']);

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }
        $this->putJson(route('penerimaan.post', $receipt->id), ['diskon_untuk' => 'pasien'])->assertOk();
        $this->assertSame('posted', $receipt->fresh()->status);
        $this->putJson(route('pembelian.reopenApproval', $this->order->id))->assertUnprocessable();
        $this->getJson(route('pembelian.edit', $this->order->id))->assertUnprocessable();
        $this->putJson(route('pembelian.update', $this->order->id), $changes)->assertUnprocessable();
    }

    public function test_removed_medicines_keep_their_draft_snapshot_until_review_and_new_items_can_be_received(): void
    {
        $receipt = $this->saveReceipt([2]);
        $oldDetail = $receipt->details()->firstOrFail();
        $this->putJson(route('pembelian.reopenApproval', $this->order->id))->assertOk();
        $newDetail = $this->addMedicine('B');
        $payload = $this->orderPayload([$newDetail]);
        $this->putJson(route('pembelian.update', $this->order->id), $payload)->assertOk();
        $this->assertNull($oldDetail->fresh()->purchase_order_detail_id);
        $this->assertSame('BATCH-0', $oldDetail->fresh()->no_batch);
        $this->assertSame('draft', $receipt->fresh()->status);
        $this->putJson(route('pembelian.approve', $this->order->id))->assertOk();
        $this->getJson(route('penerimaan.edit', $receipt->id))->assertOk()
            ->assertJsonCount(1, 'header.details')->assertJsonCount(1, 'po_payload.details')
            ->assertJsonPath('po_payload.details.0.id', $newDetail->id);

        $this->putJson(route('penerimaan.update', $receipt->id), $this->receiptPayload([2]))->assertOk();
        $this->assertDatabaseMissing('penerimaan_barang_detail', ['id' => $oldDetail->id]);
        $this->assertSame($newDetail->id, $receipt->details()->firstOrFail()->purchase_order_detail_id);
        $this->assertFalse($receipt->fresh()->requires_po_review);
    }

    public function test_quantity_reductions_are_allowed_but_draft_must_be_corrected_before_posting(): void
    {
        $receipt = $this->saveReceipt([5]);
        $this->putJson(route('pembelian.reopenApproval', $this->order->id))->assertOk();
        $payload = $this->orderPayload();
        $payload['qty'] = [3];
        $payload['save_as_draft'] = true;
        $this->putJson(route('pembelian.update', $this->order->id), $payload)->assertOk();
        $this->assertSame('draft', $this->order->fresh()->status);
        $payload['save_as_draft'] = false;
        $this->putJson(route('pembelian.update', $this->order->id), $payload)->assertOk();
        $this->putJson(route('pembelian.approve', $this->order->id))->assertOk();
        $this->putJson(route('penerimaan.update', $receipt->id), $this->receiptPayload([5]))
            ->assertUnprocessable()->assertJsonValidationErrors('qty_diterima.0');
        $this->assertTrue($receipt->fresh()->requires_po_review);
        $this->putJson(route('penerimaan.update', $receipt->id), $this->receiptPayload([3]))->assertOk();
        $this->assertFalse($receipt->fresh()->requires_po_review);
    }

    public function test_unit_and_supplier_changes_are_recomputed_when_the_draft_is_saved(): void
    {
        $receipt = $this->saveReceipt([2]);
        $this->putJson(route('pembelian.reopenApproval', $this->order->id))->assertOk();
        $detail = $this->order->details()->firstOrFail();
        $box = SatuansModel::create(['kode' => 'REV-BOX', 'nama' => 'BOX', 'is_active' => true]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $detail->obat_id, 'satuan_id' => $box->id, 'konversi' => 10,
        ]);
        $supplier = DistributorModel::create(['kode' => 'REV-NEW', 'nama' => 'Supplier Baru', 'is_active' => true]);
        $payload = $this->orderPayload();
        $payload['satuan_id'] = [$conversion->id];
        $payload['distributor_id'] = $supplier->id;
        $this->putJson(route('pembelian.update', $this->order->id), $payload)->assertOk();
        $this->putJson(route('pembelian.approve', $this->order->id))->assertOk();
        $this->getJson(route('penerimaan.edit', $receipt->id))->assertOk()
            ->assertJsonPath('header.details.0.satuan_beli', 'PCS')
            ->assertJsonPath('po_payload.details.0.satuan', 'BOX');
        $this->putJson(route('penerimaan.update', $receipt->id), $this->receiptPayload([2]))->assertOk();
        $this->assertSame($supplier->id, $receipt->fresh()->distributor_id);
        $this->assertSame('BOX', $receipt->details()->firstOrFail()->satuan_beli);
        $this->assertEquals(20, $receipt->details()->firstOrFail()->qty_diterima_stok);
        $this->assertEquals(10, $receipt->details()->firstOrFail()->harga_beli_stok);
    }

    #[DataProvider('blockingReceipts')]
    public function test_any_non_draft_or_previously_posted_receipt_blocks_revision(string $status, bool $wasPosted): void
    {
        $this->saveReceipt([1]);
        $blocking = $this->saveReceipt([1]);
        $blocking->update(['status' => $status, 'posted_at' => $wasPosted ? now() : null]);
        $this->putJson(route('pembelian.reopenApproval', $this->order->id))->assertUnprocessable();
        $this->getJson(route('pembelian.edit', $this->order->id))->assertUnprocessable();
        $this->putJson(route('pembelian.update', $this->order->id), $this->orderPayload())->assertUnprocessable();
        $actions = $this->getJson(route('pembelian.table'))->assertOk()->json('data.0.actions');
        $this->assertStringNotContainsString('reopenPembelian(', $actions);
        $this->assertSame('approved', $this->order->fresh()->status);
    }

    public static function blockingReceipts(): array
    {
        return [['posted', true], ['posted', false], ['cancelled', true], ['cancelled', false], ['draft', true]];
    }

    public function test_revision_requires_approver_access_and_cannot_cross_branches(): void
    {
        $this->saveReceipt([2]);
        $user = User::factory()->create();
        $user->branches()->attach($this->order->branch_id);
        $this->actingAs($user)->putJson(route('pembelian.reopenApproval', $this->order->id))->assertForbidden();
        $otherApprover = User::factory()->create();
        $otherApprover->assignRole('Admin');
        $otherBranch = BranchModel::create(['code' => 'REV-OTHER', 'name' => 'Cabang Lain', 'is_active' => true]);
        $otherApprover->branches()->attach($otherBranch->id);
        $this->actingAs($otherApprover);
        $this->putJson(route('pembelian.reopenApproval', $this->order->id))->assertNotFound();
    }

    private function addMedicine(string $suffix): PembelianDetailModel
    {
        $medicine = MasterObatModel::create([
            'kode_obat' => 'REV-'.$suffix, 'nama_obat' => 'Obat '.$suffix,
            'satuan_id' => $this->unit->id, 'is_active' => true,
        ]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id, 'satuan_id' => $this->unit->id, 'konversi' => 1, 'is_default' => true,
        ]);

        return $this->order->details()->create([
            'obat_id' => $medicine->id, 'satuan_konversi' => $conversion->id,
            'qty' => 10, 'harga_estimasi' => 100, 'subtotal' => 1000, 'ppn' => 0,
        ]);
    }

    private function orderPayload(?array $details = null): array
    {
        $details ??= $this->order->details()->get()->all();

        return [
            'distributor_id' => $this->order->fresh()->distributor_id, 'tanggal' => '22-10-2026',
            'total_estimasi' => 0, 'save_as_draft' => false,
            'obat_id' => array_map(fn ($detail) => $detail->obat_id, $details),
            'satuan_id' => array_map(fn ($detail) => $detail->satuan_konversi, $details),
            'qty' => array_map(fn ($detail) => $detail->qty, $details),
            'harga_estimasi' => array_map(fn ($detail) => $detail->harga_estimasi, $details),
            ...array_fill_keys(['diskon_1', 'diskon_2', 'diskon_3', 'ppn', 'subtotal'], array_fill(0, count($details), 0)),
        ];
    }

    private function receiptPayload(array $quantities): array
    {
        $detail = $this->order->details()->firstOrFail();
        $count = count($quantities);

        return [
            'purchase_order_id' => $this->order->id, 'purchase_order_revision' => (int) $this->order->fresh()->revision,
            'nomor_faktur' => 'INV-REV', 'tanggal_penerimaan' => '22-10-2026', 'tanggal_faktur' => '22-10-2026',
            'purchase_order_detail_id' => array_fill(0, $count, $detail->id),
            'obat_id' => array_fill(0, $count, $detail->obat_id), 'qty_diterima' => $quantities,
            'harga_beli' => array_fill(0, $count, 100), 'ppn' => array_fill(0, $count, 0),
            'no_batch' => array_map(fn ($index) => 'BATCH-'.$index, array_keys($quantities)),
            'expired_date' => array_fill(0, $count, '22-10-2027'),
        ];
    }

    private function saveReceipt(array $quantities): PenerimaanBarangModel
    {
        $response = $this->postJson(route('penerimaan.store'), $this->receiptPayload($quantities))->assertOk();

        return PenerimaanBarangModel::findOrFail($response->json('data.id'));
    }
}
