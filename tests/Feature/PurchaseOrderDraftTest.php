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
use App\Services\Notifikasi\TransactionNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchaseOrderDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_refresh_requires_login_and_does_not_cache_the_token(): void
    {
        $this->getJson(route('session.csrf-token'))->assertUnauthorized();
        $user = User::factory()->create();
        $response = $this->actingAs($user)->withSession(['_token' => 'current-session-token'])
            ->getJson(route('session.csrf-token'))->assertOk()
            ->assertJsonPath('token', 'current-session-token')->assertJsonPath('user_id', $user->id);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_repeating_a_create_after_a_lost_response_returns_the_same_po(): void
    {
        Notification::fake();
        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $payload = [...$this->payload($distributor, $medicine, $conversion), 'save_as_draft' => true];
        $headers = ['Idempotency-Key' => (string) Str::uuid(), 'X-PO-User' => $user->id];
        $first = $this->actingAs($user)->postJson(route('pembelian.store'), $payload, $headers)->assertOk();
        $second = $this->postJson(route('pembelian.store'), $payload, $headers)->assertOk();
        $this->assertSame($first->json(), $second->json());
        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseCount('purchase_order_details', 1);
        $this->assertDatabaseCount('purchase_order_save_requests', 1);

        $this->postJson(route('pembelian.store'), [...$payload, 'qty' => [2]], $headers)->assertConflict();
        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_repeating_an_update_does_not_replace_details_or_send_notifications_again(): void
    {
        Notification::fake();
        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $payload = $this->payload($distributor, $medicine, $conversion);
        $id = $this->actingAs($user)->postJson(route('pembelian.store'), [...$payload, 'save_as_draft' => true])
            ->assertOk()->json('data.id');
        $this->partialMock(TransactionNotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyApprovalRequest')->once();
        });
        $headers = ['Idempotency-Key' => (string) Str::uuid(), 'X-PO-User' => $user->id];
        $this->putJson(route('pembelian.update', $id), $payload, $headers)->assertOk();
        $detailId = PembelianDetailModel::firstOrFail()->id;
        $this->putJson(route('pembelian.update', $id), $payload, $headers)->assertOk();
        $this->assertSame($detailId, PembelianDetailModel::firstOrFail()->id);
        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_failed_validation_can_be_corrected_and_retried(): void
    {
        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $payload = [...$this->payload($distributor, $medicine, $conversion), 'save_as_draft' => true];
        $headers = ['Idempotency-Key' => (string) Str::uuid(), 'X-PO-User' => $user->id];
        $this->actingAs($user)->postJson(route('pembelian.store'), [...$payload, 'qty' => [0]], $headers)->assertUnprocessable();
        $this->assertDatabaseCount('purchase_order_save_requests', 0);
        $this->postJson(route('pembelian.store'), $payload, $headers)->assertOk();
        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_saving_from_a_page_opened_by_another_account_is_rejected(): void
    {
        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $this->actingAs($user)->postJson(route('pembelian.store'), $this->payload($distributor, $medicine, $conversion), [
            'Idempotency-Key' => (string) Str::uuid(), 'X-PO-User' => $user->id + 1,
        ])->assertForbidden();
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_notification_failure_does_not_turn_a_committed_po_into_a_failed_save(): void
    {
        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $this->partialMock(TransactionNotificationService::class, function ($mock) {
            $mock->shouldReceive('isApprovalRole')->andReturn(false);
            $mock->shouldReceive('notifyApprovalRequest')->once()->andThrow(new \RuntimeException('Notification unavailable'));
        });
        $this->actingAs($user)->postJson(route('pembelian.store'), $this->payload($distributor, $medicine, $conversion), [
            'Idempotency-Key' => (string) Str::uuid(), 'X-PO-User' => $user->id,
        ])->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseCount('purchase_order_details', 1);
        $this->assertDatabaseCount('purchase_order_save_requests', 1);
    }

    public function test_real_csrf_rejection_can_refresh_the_token_and_save_without_reloading(): void
    {
        $this->app->bind(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, function ($app) {
            return new class($app, $app['encrypter']) extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $payload = [...$this->payload($distributor, $medicine, $conversion), 'save_as_draft' => true];
        $headers = ['Idempotency-Key' => (string) Str::uuid(), 'X-PO-User' => $user->id, 'X-CSRF-TOKEN' => 'stale-token'];
        $this->actingAs($user)->withSession(['_token' => 'fresh-token'])
            ->postJson(route('pembelian.store'), $payload, $headers)->assertStatus(419);
        $this->assertDatabaseCount('purchase_orders', 0);
        $token = $this->getJson(route('session.csrf-token'))->assertOk()->json('token');
        $this->postJson(route('pembelian.store'), $payload, [...$headers, 'X-CSRF-TOKEN' => $token])->assertOk();
        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_a_previous_response_cannot_be_replayed_after_branch_access_is_revoked(): void
    {
        [$user, $distributor, $medicine, $conversion] = $this->purchaseContext();
        $payload = [...$this->payload($distributor, $medicine, $conversion), 'save_as_draft' => true];
        $headers = ['Idempotency-Key' => (string) Str::uuid(), 'X-PO-User' => $user->id];
        $this->actingAs($user)->postJson(route('pembelian.store'), $payload, $headers)->assertOk();
        $user->branches()->detach();
        $user->unsetRelation('branches');
        $this->postJson(route('pembelian.store'), $payload, $headers)->assertConflict();
        $this->assertDatabaseCount('purchase_orders', 1);
    }

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
