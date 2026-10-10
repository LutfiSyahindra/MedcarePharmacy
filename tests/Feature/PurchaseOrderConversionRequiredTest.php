<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderConversionRequiredTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('missingConversions')]
    public function test_missing_conversion_blocks_submission_even_for_approvers(mixed $unitValue, bool $approver): void
    {
        Notification::fake();
        [$user, $payload, $conversion] = $this->purchaseContext();
        $conversion->delete();

        if ($approver) {
            Role::create(['name' => 'Admin', 'guard_name' => 'web']);
            $user->assignRole('Admin');
        }

        $payload['satuan_id'] = [$unitValue];

        $this->actingAs($user)->postJson(route('pembelian.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('satuan_id.0')
            ->assertJsonPath('errors', fn (array $errors) => $errors['satuan_id.0'][0] === 'Lengkapi satuan konversi untuk item obat ke-1. PO hanya dapat disimpan sebagai draft sebelum satuan konversi lengkap.');

        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('purchase_order_details', 0);
        Notification::assertNothingSent();
    }

    public static function missingConversions(): array
    {
        return [
            'base unit' => [0, false],
            'null' => [null, false],
            'empty' => ['', false],
            'approver base unit' => [0, true],
            'approver null' => [null, true],
            'approver empty' => ['', true],
        ];
    }

    public function test_mixed_po_can_only_be_saved_as_draft_until_every_conversion_is_complete(): void
    {
        Notification::fake();
        [$user, $payload, $conversion] = $this->purchaseContext();
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-PO-MISSING-02',
            'nama_obat' => 'Obat Tanpa Konversi',
            'satuan_id' => $conversion->satuan_id,
            'distributor_id' => $payload['distributor_id'],
            'is_active' => true,
        ]);

        foreach (['obat_id', 'qty', 'harga_estimasi', 'diskon_1', 'diskon_2', 'diskon_3', 'ppn', 'subtotal', 'satuan_id'] as $field) {
            $payload[$field][] = $payload[$field][0];
        }
        $payload['obat_id'][1] = $medicine->id;
        $payload['satuan_id'][1] = 0;

        $this->actingAs($user)->postJson(route('pembelian.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('satuan_id.1');
        $this->assertDatabaseCount('purchase_orders', 0);

        $response = $this->actingAs($user)->postJson(route('pembelian.store'), [
            ...$payload,
            'save_as_draft' => true,
        ])->assertOk()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('warning', 'PO hanya dapat disimpan sebagai draft. Lengkapi satuan konversi di Master Data sebelum memproses PO.');

        $purchaseOrder = PembelianModel::findOrFail($response->json('data.id'));
        $originalDetailIds = $purchaseOrder->details()->pluck('id')->all();
        $this->assertNull($purchaseOrder->approved_by);
        $this->assertSame($conversion->id, $purchaseOrder->details()->firstOrFail()->satuan_konversi);
        $this->assertNull($purchaseOrder->details()->where('obat_id', $medicine->id)->firstOrFail()->satuan_konversi);
        Notification::assertNothingSent();

        $payload['qty'][1] = 2;
        $this->actingAs($user)->putJson(route('pembelian.update', $purchaseOrder->id), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('satuan_id.1');

        $this->assertSame('draft', $purchaseOrder->fresh()->status);
        $this->assertSame($originalDetailIds, $purchaseOrder->details()->pluck('id')->all());
        $this->assertSame('1.00', $purchaseOrder->details()->where('obat_id', $medicine->id)->firstOrFail()->qty);

        $this->actingAs($user)->putJson(route('pembelian.update', $purchaseOrder->id), [
            ...$payload,
            'save_as_draft' => true,
        ])->assertOk()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('warning', 'PO hanya dapat disimpan sebagai draft. Lengkapi satuan konversi di Master Data sebelum memproses PO.');
        $this->assertSame('2.00', $purchaseOrder->details()->where('obat_id', $medicine->id)->firstOrFail()->qty);
        Notification::assertNothingSent();

        $newConversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id,
            'satuan_id' => $conversion->satuan_id,
            'konversi' => 1,
            'is_default' => true,
        ]);
        $payload['satuan_id'][1] = $newConversion->id;

        $this->actingAs($user)->putJson(route('pembelian.update', $purchaseOrder->id), $payload)
            ->assertOk()
            ->assertJsonPath('data.status', 'waiting_approval')
            ->assertJsonPath('warning', null);
        $this->assertSame(2, $purchaseOrder->details()->count());
        $this->assertSame('waiting_approval', $purchaseOrder->fresh()->status);

        $approver = $this->approverFor($user);
        $this->actingAs($approver)->putJson(route('pembelian.approve', $purchaseOrder->id))->assertOk();
        $this->assertSame('approved', $purchaseOrder->fresh()->status);
        $this->assertSame($approver->id, $purchaseOrder->fresh()->approved_by);
    }

    public function test_approver_can_save_missing_conversion_as_draft_without_auto_approval(): void
    {
        Notification::fake();
        [$user, $payload, $conversion] = $this->purchaseContext();
        $conversion->delete();
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $user->assignRole('Admin');
        $payload['satuan_id'] = [null];

        $response = $this->actingAs($user)->postJson(route('pembelian.store'), [
            ...$payload,
            'save_as_draft' => true,
        ])->assertOk()->assertJsonPath('data.status', 'draft');

        $purchaseOrder = PembelianModel::findOrFail($response->json('data.id'));
        $this->assertNull($purchaseOrder->approved_by);
        $this->assertNull($purchaseOrder->details()->firstOrFail()->satuan_konversi);
        $this->assertNotEmpty($response->json('warning'));
        Notification::assertNothingSent();
    }

    public function test_approval_is_blocked_if_the_selected_conversion_was_deleted(): void
    {
        Notification::fake();
        [$user, $payload, $conversion] = $this->purchaseContext();
        $response = $this->actingAs($user)->postJson(route('pembelian.store'), $payload)->assertOk();
        $purchaseOrder = PembelianModel::findOrFail($response->json('data.id'));
        $conversion->delete();
        $approver = $this->approverFor($user);

        $this->actingAs($approver)->putJson(route('pembelian.approve', $purchaseOrder->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('satuan_id.0');
        $this->assertSame('waiting_approval', $purchaseOrder->fresh()->status);
        $this->assertNull($purchaseOrder->fresh()->approved_by);
        Notification::assertNothingSent();
    }

    #[DataProvider('invalidConversions')]
    public function test_invalid_selected_conversion_cannot_be_saved(string $invalidKind, bool $draft): void
    {
        Notification::fake();
        [$user, $payload, $conversion] = $this->purchaseContext();

        if ($invalidKind === 'missing') {
            $payload['satuan_id'] = [$conversion->id + 1000];
        } elseif ($invalidKind === 'zero factor') {
            $conversion->update(['konversi' => 0]);
        } else {
            $otherMedicine = MasterObatModel::create([
                'kode_obat' => 'OBT-PO-OTHER',
                'nama_obat' => 'Obat Lain',
                'satuan_id' => $conversion->satuan_id,
                'is_active' => true,
            ]);
            $payload['obat_id'] = [$otherMedicine->id];
        }

        $this->actingAs($user)->postJson(route('pembelian.store'), [
            ...$payload,
            'save_as_draft' => $draft,
        ])->assertUnprocessable()->assertJsonValidationErrors('satuan_id.0');
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public static function invalidConversions(): array
    {
        return [
            'missing' => ['missing', false],
            'zero factor' => ['zero factor', false],
            'other medicine' => ['other medicine', false],
            'draft missing' => ['missing', true],
            'draft zero factor' => ['zero factor', true],
            'draft other medicine' => ['other medicine', true],
        ];
    }

    private function approverFor(User $creator): User
    {
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $approver = User::factory()->create();
        $approver->assignRole('Admin');
        $approver->branches()->attach($creator->branches()->pluck('branches.id'));

        return $approver;
    }

    private function purchaseContext(): array
    {
        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-PO-CONVERSION',
            'name' => 'Cabang PO Konversi',
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);
        $unit = SatuansModel::create(['kode' => 'PCS-PO-CONVERSION', 'nama' => 'PCS', 'is_active' => true]);
        $distributor = DistributorModel::create([
            'kode' => 'DST-PO-CONVERSION',
            'nama' => 'Distributor PO Konversi',
            'is_active' => true,
        ]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-PO-CONVERSION',
            'nama_obat' => 'Obat PO Konversi',
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

        return [$user, [
            'no_po' => 'PO-CONVERSION-001',
            'distributor_id' => $distributor->id,
            'tanggal' => '10-10-2026',
            'total_estimasi' => 1000,
            'obat_id' => [$medicine->id],
            'qty' => [1],
            'harga_estimasi' => [1000],
            'diskon_1' => [0],
            'diskon_2' => [0],
            'diskon_3' => [0],
            'ppn' => [0],
            'subtotal' => [1000],
            'satuan_id' => [$conversion->id],
        ], $conversion];
    }
}
