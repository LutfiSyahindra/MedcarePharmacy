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
use App\Services\Menu\PembelianPenerimaan\PurchaseReceiptNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class PurchaseReceiptNumberingTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private BranchModel $branch;

    private DistributorModel $supplier;

    private MasterObatModel $medicine;

    private KonversiSatuanModel $conversion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 13)->setTime(9, 0));
        Notification::fake();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
        $this->branch = BranchModel::create(['code' => 'NUMBER', 'name' => 'Cabang Nomor', 'is_active' => true]);
        $this->userA->branches()->attach($this->branch);
        $this->userB->branches()->attach($this->branch);
        $this->supplier = DistributorModel::create(['kode' => 'NUMBER', 'nama' => 'Supplier Nomor', 'is_active' => true]);
        $unit = SatuansModel::create(['kode' => 'NUMBER', 'nama' => 'PCS', 'is_active' => true]);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'NUMBER', 'nama_obat' => 'Obat Nomor', 'satuan_id' => $unit->id,
            'distributor_id' => $this->supplier->id, 'is_active' => true,
        ]);
        $this->conversion = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $unit->id, 'konversi' => 1, 'is_default' => true,
        ]);
    }

    public function test_two_users_can_submit_the_same_po_preview_in_reverse_order(): void
    {
        $otherBranch = BranchModel::create(['code' => 'NUMBER-B', 'name' => 'Cabang B', 'is_active' => true]);
        $this->userB->branches()->sync([$otherBranch->id]);
        $previewA = $this->actingAs($this->userA)->getJson(route('pembelian.generateNoPO'))->assertOk()->json();
        $previewB = $this->actingAs($this->userB)->getJson(route('pembelian.generateNoPO'))->assertOk()->json();
        $this->assertSame('PO-2026-10-0001', $previewA);
        $this->assertSame($previewA, $previewB);
        $this->assertDatabaseCount('purchase_receipt_number_sequences', 0);

        $this->actingAs($this->userB)->postJson(route('pembelian.store'), [
            ...$this->poPayload($previewB), 'save_as_draft' => false,
        ])
            ->assertOk()->assertJsonPath('data.no_po', 'PO-2026-10-0001');
        $this->actingAs($this->userA)->postJson(route('pembelian.store'), [
            ...$this->poPayload($previewA), 'save_as_draft' => false,
        ])
            ->assertOk()->assertJsonPath('data.no_po', 'PO-2026-10-0002');

        $this->assertDatabaseHas('purchase_orders', [
            'no_po' => 'PO-2026-10-0001', 'created_by' => $this->userB->id, 'branch_id' => $otherBranch->id,
        ]);
        $this->assertDatabaseHas('purchase_orders', ['no_po' => 'PO-2026-10-0002', 'created_by' => $this->userA->id]);
        $this->assertDatabaseCount('purchase_order_details', 2);
    }

    public function test_automatic_po_can_save_without_loading_a_preview_and_keeps_its_number_on_edit(): void
    {
        $payload = $this->poPayload();
        unset($payload['no_po']);
        $response = $this->actingAs($this->userA)->postJson(route('pembelian.store'), $payload)
            ->assertOk()->assertJsonPath('data.no_po', 'PO-2026-10-0001');

        $payload['no_po'] = 'PO-2026-10-9000';
        $payload['catatan'] = 'Draft diperbarui';
        $this->putJson(route('pembelian.update', $response->json('data.id')), $payload)
            ->assertOk()->assertJsonPath('data.no_po', 'PO-2026-10-0001');
        $this->assertDatabaseHas('purchase_orders', ['no_po' => 'PO-2026-10-0001', 'catatan' => 'Draft diperbarui']);
        $this->assertDatabaseHas('purchase_receipt_number_sequences', ['prefix' => 'PO-2026-10-', 'last_number' => 1]);
    }

    public function test_two_users_can_submit_the_same_receipt_preview_for_different_pos(): void
    {
        $poA = $this->approvedOrder('PO-A');
        $poB = $this->approvedOrder('PO-B');
        $previewA = $this->actingAs($this->userA)->getJson(route('penerimaan.generateNoPenerimaan'))->assertOk()->json();
        $previewB = $this->actingAs($this->userB)->getJson(route('penerimaan.generateNoPenerimaan'))->assertOk()->json();
        $this->assertSame('PB-2026-10-0001', $previewA);
        $this->assertSame($previewA, $previewB);
        $this->assertDatabaseCount('purchase_receipt_number_sequences', 0);

        $this->actingAs($this->userB)->postJson(route('penerimaan.store'), $this->receiptPayload($poB, $previewB))
            ->assertOk()->assertJsonPath('data.nomor_penerimaan', 'PB-2026-10-0001');
        $this->actingAs($this->userA)->postJson(route('penerimaan.store'), $this->receiptPayload($poA, $previewA))
            ->assertOk()->assertJsonPath('data.nomor_penerimaan', 'PB-2026-10-0002');
        $this->assertDatabaseHas('penerimaan_barang', ['nomor_penerimaan' => 'PB-2026-10-0002', 'created_by' => $this->userA->id]);
        $this->assertDatabaseCount('penerimaan_barang_detail', 2);
    }

    public function test_receipt_can_save_without_a_preview_and_keeps_its_number_on_edit(): void
    {
        $payload = $this->receiptPayload($this->approvedOrder('PO-EDIT'));
        unset($payload['nomor_penerimaan']);
        $response = $this->actingAs($this->userA)->postJson(route('penerimaan.store'), $payload)
            ->assertOk()->assertJsonPath('data.nomor_penerimaan', 'PB-2026-10-0001');
        $payload['nomor_penerimaan'] = 'PB-2026-10-9000';
        $payload['catatan'] = 'Penerimaan diperbarui';
        $this->putJson(route('penerimaan.update', $response->json('data.id')), $payload)
            ->assertOk()->assertJsonPath('data.nomor_penerimaan', 'PB-2026-10-0001');
        $this->assertDatabaseHas('penerimaan_barang', ['nomor_penerimaan' => 'PB-2026-10-0001', 'catatan' => 'Penerimaan diperbarui']);
    }

    public function test_manual_po_numbers_remain_required_unique_and_unchanged(): void
    {
        $this->supplier->update(['uses_manual_po_number' => true]);
        $this->actingAs($this->userA)->postJson(route('pembelian.store'), $this->poPayload())
            ->assertUnprocessable()->assertJsonValidationErrors('no_po');
        $this->postJson(route('pembelian.store'), $this->poPayload('SUPPLIER-001'))
            ->assertOk()->assertJsonPath('data.no_po', 'SUPPLIER-001');
        $this->actingAs($this->userB)->postJson(route('pembelian.store'), $this->poPayload('SUPPLIER-001'))
            ->assertUnprocessable()->assertJsonValidationErrors('no_po');
        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_numbering_continues_from_existing_numbers_and_does_not_reuse_deleted_numbers(): void
    {
        $this->approvedOrder('PO-2026-10-10000');
        $this->approvedOrder('PO-2026-10-0002');
        $this->assertSame('PO-2026-10-10001', $this->actingAs($this->userA)->getJson(route('pembelian.generateNoPO'))
            ->assertOk()->json());
        $response = $this->postJson(route('pembelian.store'), $this->poPayload('PO-2026-10-0003'))
            ->assertOk()->assertJsonPath('data.no_po', 'PO-2026-10-10001');
        PembelianModel::findOrFail($response->json('data.id'))->delete();
        $this->postJson(route('pembelian.store'), $this->poPayload())
            ->assertOk()->assertJsonPath('data.no_po', 'PO-2026-10-10002');
    }

    public function test_manual_po_using_the_automatic_format_is_skipped_by_automatic_numbering(): void
    {
        $this->supplier->update(['uses_manual_po_number' => true]);
        $this->actingAs($this->userA)->postJson(route('pembelian.store'), $this->poPayload('PO-2026-10-0007'))
            ->assertOk()->assertJsonPath('data.no_po', 'PO-2026-10-0007');
        $this->supplier->update(['uses_manual_po_number' => false]);
        $this->postJson(route('pembelian.store'), $this->poPayload('PO-2026-10-0001'))
            ->assertOk()->assertJsonPath('data.no_po', 'PO-2026-10-0008');
    }

    public function test_receipt_numbering_uses_the_highest_suffix_and_resets_each_month(): void
    {
        $po = $this->approvedOrder('PO-LEGACY');
        foreach (['PB-2026-10-10000', 'PB-2026-10-0002', 'PB-2026-10-CUSTOM'] as $number) {
            PenerimaanBarangModel::create([
                'nomor_penerimaan' => $number, 'purchase_order_id' => $po->id,
                'distributor_id' => $this->supplier->id, 'nomor_faktur' => $number,
                'tanggal_penerimaan' => '2026-10-13', 'created_by' => $this->userA->id,
            ]);
        }
        $this->assertSame('PB-2026-10-10001', $this->actingAs($this->userA)->getJson(route('penerimaan.generateNoPenerimaan'))
            ->assertOk()->json());
        $this->postJson(route('penerimaan.store'), $this->receiptPayload($po))
            ->assertOk()->assertJsonPath('data.nomor_penerimaan', 'PB-2026-10-10001');
        $this->travelTo(now()->addMonth());
        $this->assertSame('PB-2026-11-0001', $this->getJson(route('penerimaan.generateNoPenerimaan'))->assertOk()->json());
    }

    public function test_a_rolled_back_transaction_does_not_consume_numbers(): void
    {
        $numbering = app(PurchaseReceiptNumberService::class);
        try {
            DB::transaction(function () use ($numbering) {
                $this->assertSame('PO-2026-10-0001', $numbering->nextPurchaseOrder());
                $this->assertSame('PB-2026-10-0001', $numbering->nextReceipt());
                throw new RuntimeException('Simulated save failure');
            });
            $this->fail('The transaction should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated save failure', $exception->getMessage());
        }
        $this->assertDatabaseCount('purchase_receipt_number_sequences', 0);
        $this->assertSame('PO-2026-10-0001', $numbering->previewPurchaseOrder());
        $this->assertSame('PB-2026-10-0001', $numbering->previewReceipt());
    }

    private function poPayload(string $number = ''): array
    {
        return [
            'no_po' => $number, 'distributor_id' => $this->supplier->id, 'tanggal' => '13-10-2026',
            'save_as_draft' => true, 'total_estimasi' => 1000,
            'obat_id' => [$this->medicine->id], 'qty' => [1], 'harga_estimasi' => [1000],
            'diskon_1' => [0], 'diskon_2' => [0], 'diskon_3' => [0], 'subtotal' => [1000],
            'satuan_id' => [$this->conversion->id],
        ];
    }

    private function approvedOrder(string $number): PembelianModel
    {
        $po = PembelianModel::create([
            'no_po' => $number, 'distributor_id' => $this->supplier->id, 'branch_id' => $this->branch->id,
            'tanggal_po' => '2026-10-13', 'status' => 'approved', 'created_by' => $this->userA->id,
        ]);
        $po->details()->create([
            'obat_id' => $this->medicine->id, 'qty' => 10, 'harga_estimasi' => 1000,
            'satuan_konversi' => $this->conversion->id,
        ]);

        return $po;
    }

    private function receiptPayload(PembelianModel $po, string $number = ''): array
    {
        return [
            'nomor_penerimaan' => $number, 'purchase_order_id' => $po->id, 'nomor_faktur' => 'INV-'.$po->id,
            'tanggal_penerimaan' => '13-10-2026', 'tanggal_faktur' => '13-10-2026',
            'purchase_order_detail_id' => [$po->details()->firstOrFail()->id], 'obat_id' => [$this->medicine->id],
            'qty_diterima' => [1], 'no_batch' => ['BATCH-'.$po->id], 'expired_date' => ['13-10-2027'],
            'harga_beli' => [1000], 'ppn' => [0],
        ];
    }
}
