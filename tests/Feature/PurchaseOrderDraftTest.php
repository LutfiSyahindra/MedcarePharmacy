<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PurchaseOrderDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_large_json_draft_persists_every_item_with_aligned_quantities_and_prices(): void
    {
        Notification::fake();
        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $payload = $this->payload($distributor, $medicine, $conversion);
        foreach (['obat_id', 'qty', 'harga_estimasi', 'diskon_1', 'diskon_2', 'diskon_3', 'ppn', 'subtotal', 'satuan_id'] as $field) {
            $payload[$field] = [];
        }
        $expectedTotal = 0;
        for ($index = 1; $index <= 300; $index++) {
            $item = $index === 1 ? $medicine : MasterObatModel::create([
                'kode_obat' => 'PO-BULK-'.$index,
                'nama_obat' => 'Obat PO Bulk '.$index,
                'satuan_id' => $medicine->satuan_id,
                'is_active' => true,
            ]);
            $unit = $index === 1 ? $conversion : KonversiSatuanModel::create([
                'obat_id' => $item->id,
                'satuan_id' => $medicine->satuan_id,
                'konversi' => 1,
                'is_default' => true,
            ]);
            $payload['obat_id'][] = $item->id;
            $payload['satuan_id'][] = $unit->id;
            $payload['qty'][] = $index;
            $payload['harga_estimasi'][] = 100 + $index;
            $payload['subtotal'][] = $index * (100 + $index);
            foreach (['diskon_1', 'diskon_2', 'diskon_3', 'ppn'] as $field) {
                $payload[$field][] = 0;
            }
            $expectedTotal += $index * (100 + $index);
        }

        $response = $this->actingAs($user)->postJson(route('pembelian.store'), [
            ...$payload,
            'save_as_draft' => true,
        ])->assertOk()->assertJsonPath('data.status', 'draft');

        $order = PembelianModel::findOrFail($response->json('data.id'));
        $details = PembelianDetailModel::where('purchase_order_id', $order->id)->orderBy('id')->get();
        $this->assertCount(300, $details);
        $this->assertEquals(45150, $details->sum('qty'));
        $this->assertEquals(300, $details->last()->qty);
        $this->assertEquals(400, $details->last()->harga_estimasi);
        $this->assertEquals($expectedTotal, $order->total_estimasi);
        Notification::assertNothingSent();
    }

    public function test_purchase_order_can_be_saved_as_draft_then_submitted_for_approval(): void
    {
        Notification::fake();

        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $payload = $this->payload($distributor, $medicine, $conversion);

        $response = $this->actingAs($user)
            ->postJson(route('pembelian.store'), [
                ...$payload,
                'save_as_draft' => true,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'draft');

        $purchaseOrder = PembelianModel::findOrFail($response->json('data.id'));
        $purchaseDetail = PembelianDetailModel::where('purchase_order_id', $purchaseOrder->id)->firstOrFail();

        $this->assertSame('draft', $purchaseOrder->status);
        $this->assertNull($purchaseOrder->approved_by);
        $this->assertSame('11.00', $purchaseDetail->ppn);
        $this->assertSame('1110.00', $purchaseDetail->subtotal);
        $this->assertSame('1110.00', $purchaseOrder->total_estimasi);
        Notification::assertNothingSent();

        $this->actingAs($user)
            ->putJson(route('pembelian.update', $purchaseOrder->id), $payload)
            ->assertOk()
            ->assertJsonPath('data.status', 'waiting_approval');

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'status' => 'waiting_approval',
            'approved_by' => null,
        ]);
    }

    /**
     * @return array{0: User, 1: DistributorModel, 2: MasterObatModel, 3: KonversiSatuanModel}
     */
    private function purchaseContext(): array
    {
        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-PO-DRAFT',
            'name' => 'Cabang PO Draft',
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);
        $unit = SatuansModel::create([
            'kode' => 'PCS-PO-DRAFT',
            'nama' => 'PCS',
            'is_active' => true,
        ]);
        $distributor = DistributorModel::create([
            'kode' => 'DST-PO-DRAFT',
            'nama' => 'Distributor PO Draft',
            'is_active' => true,
        ]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-PO-DRAFT',
            'nama_obat' => 'Obat PO Draft',
            'satuan_id' => $unit->id,
            'distributor_id' => $distributor->id,
            'is_active' => true,
        ]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id,
            'satuan_id' => $unit->id,
            'konversi' => 1,
            'is_default' => true,
        ]);

        return [$user, $distributor, $medicine, $conversion];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(
        DistributorModel $distributor,
        MasterObatModel $medicine,
        KonversiSatuanModel $conversion,
    ): array {
        return [
            'no_po' => 'PO-DRAFT-001',
            'distributor_id' => $distributor->id,
            'tanggal' => '27-09-2026',
            'catatan' => 'Draft PO yang belum diajukan.',
            'total_estimasi' => 1000,
            'biaya_asuransi' => 0,
            'biaya_pengiriman' => 0,
            'obat_id' => [$medicine->id],
            'qty' => [1],
            'harga_estimasi' => [1000],
            'diskon_1' => [0],
            'diskon_2' => [0],
            'diskon_3' => [0],
            'subtotal' => [1000],
            'satuan_id' => [$conversion->id],
        ];
    }
}
