<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PurchaseOrderInlineConversionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private SatuansModel $tablet;

    private SatuansModel $box;

    private MasterObatModel $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->user = User::factory()->create();
        $this->tablet = SatuansModel::create(['kode' => 'TAB-INLINE', 'nama' => 'Tablet', 'is_active' => true]);
        $this->box = SatuansModel::create(['kode' => 'BOX-INLINE', 'nama' => 'Box', 'is_active' => true]);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-INLINE', 'nama_obat' => 'Obat Konversi Langsung',
            'satuan_id' => $this->tablet->id, 'is_active' => true,
        ]);
    }

    public function test_purchase_order_lists_active_purchase_units(): void
    {
        SatuansModel::create(['kode' => 'OLD-INLINE', 'nama' => 'Tidak Aktif', 'is_active' => false]);

        $this->actingAs($this->user)->getJson(route('pembelian.getSatuan'))
            ->assertOk()->assertJsonCount(2)
            ->assertJsonPath('0.id', $this->box->id)
            ->assertJsonPath('1.id', $this->tablet->id);
    }

    public function test_conversion_created_from_po_is_persistent_default_and_allows_immediate_submission(): void
    {
        $branch = BranchModel::create(['code' => 'CB-INLINE', 'name' => 'Cabang Inline', 'is_active' => true]);
        $this->user->branches()->attach($branch->id);
        $distributor = DistributorModel::create(['kode' => 'DST-INLINE', 'nama' => 'Distributor Inline', 'is_active' => true]);

        $response = $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.obat_id', $this->medicine->id)
            ->assertJsonPath('data.satuan_id', $this->box->id)
            ->assertJsonPath('data.konversi', 100)
            ->assertJsonPath('data.satuan.nama', 'Box');
        $conversionId = $response->json('data.id');
        $this->assertDatabaseHas('obat_satuan_conversions', [
            'id' => $conversionId, 'obat_id' => $this->medicine->id,
            'satuan_id' => $this->box->id, 'konversi' => 100, 'is_default' => true,
        ]);
        $this->getJson(route('pembelian.getKonversiSatuan', ['obat_ids' => [$this->medicine->id]]))
            ->assertOk()->assertJsonPath($this->medicine->id.'.0.id', $conversionId);

        $purchase = $this->postJson(route('pembelian.store'), [
            'no_po' => 'PO-INLINE-001', 'distributor_id' => $distributor->id, 'tanggal' => '14-10-2026',
            'total_estimasi' => 200000, 'obat_id' => [$this->medicine->id], 'qty' => [2],
            'harga_estimasi' => [100000], 'diskon_1' => [0], 'diskon_2' => [0], 'diskon_3' => [0],
            'ppn' => [0], 'subtotal' => [200000], 'satuan_id' => [$conversionId],
        ])->assertOk()->assertJsonPath('data.status', 'waiting_approval')->assertJsonPath('warning', null);
        $this->assertDatabaseHas('purchase_order_details', [
            'purchase_order_id' => $purchase->json('data.id'),
            'obat_id' => $this->medicine->id, 'satuan_konversi' => $conversionId,
            'qty' => 2, 'harga_estimasi' => 100000, 'subtotal' => 200000,
        ]);
    }

    public function test_same_stock_unit_can_be_set_as_a_one_to_one_conversion(): void
    {
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            ...$this->payload(), 'satuan_id' => $this->tablet->id, 'konversi' => 1,
        ])->assertCreated()->assertJsonPath('data.konversi', 1);
    }

    public function test_duplicate_submission_does_not_overwrite_an_existing_conversion(): void
    {
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), $this->payload())->assertCreated();
        $this->postJson(route('pembelian.storeKonversiSatuan'), [...$this->payload(), 'konversi' => 200])
            ->assertUnprocessable()->assertJsonValidationErrors('satuan_id');
        $this->assertDatabaseCount('obat_satuan_conversions', 1);
        $this->assertDatabaseHas('obat_satuan_conversions', ['obat_id' => $this->medicine->id, 'konversi' => 100, 'is_default' => true]);
    }

    public function test_retrying_the_same_conversion_returns_the_original_unit_without_duplicates(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), $this->payload())->assertCreated();
        $this->postJson(route('pembelian.storeKonversiSatuan'), $this->payload())
            ->assertOk()->assertJsonPath('data.id', $response->json('data.id'));
        $this->assertDatabaseCount('obat_satuan_conversions', 1);
    }

    public function test_adding_a_unit_preserves_an_existing_default_and_other_medicines(): void
    {
        $existing = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->tablet->id, 'konversi' => 1, 'is_default' => true,
        ]);
        $otherMedicine = MasterObatModel::create([
            'kode_obat' => 'OBT-INLINE-OTHER', 'nama_obat' => 'Obat Lain', 'satuan_id' => $this->tablet->id,
        ]);
        $other = KonversiSatuanModel::create([
            'obat_id' => $otherMedicine->id, 'satuan_id' => $this->box->id, 'konversi' => 50, 'is_default' => true,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), $this->payload())->assertCreated();
        $this->assertDatabaseHas('obat_satuan_conversions', ['id' => $response->json('data.id'), 'is_default' => false]);
        $this->assertDatabaseHas('obat_satuan_conversions', ['id' => $existing->id, 'is_default' => true]);
        $this->assertDatabaseHas('obat_satuan_conversions', ['id' => $other->id, 'konversi' => 50, 'is_default' => true]);
    }

    #[DataProvider('invalidFactors')]
    public function test_invalid_factors_cannot_be_saved(mixed $factor): void
    {
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [...$this->payload(), 'konversi' => $factor])
            ->assertUnprocessable()->assertJsonValidationErrors('konversi');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public static function invalidFactors(): array
    {
        return [[0], [-1], [1.5], [null], [2147483648]];
    }

    public function test_stock_unit_requires_factor_one(): void
    {
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            ...$this->payload(), 'satuan_id' => $this->tablet->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('konversi');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public function test_inactive_purchase_units_and_missing_stock_units_are_rejected(): void
    {
        $this->box->update(['is_active' => false]);
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), $this->payload())
            ->assertUnprocessable()->assertJsonValidationErrors('satuan_id');
        $this->box->update(['is_active' => true]);
        $this->medicine->update(['satuan_id' => null]);
        $this->postJson(route('pembelian.storeKonversiSatuan'), $this->payload())
            ->assertUnprocessable()->assertJsonValidationErrors('obat_id');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public function test_unit_endpoints_require_authentication(): void
    {
        $this->getJson(route('pembelian.getSatuan'))->assertUnauthorized();
        $this->postJson(route('pembelian.storeKonversiSatuan'), $this->payload())->assertUnauthorized();
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public function test_modal_can_save_multiple_units_and_select_a_purchase_unit_as_the_first_default(): void
    {
        $strip = SatuansModel::create(['kode' => 'STR-INLINE', 'nama' => 'Strip', 'is_active' => true]);
        $response = $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['satuan_id' => $this->tablet->id, 'konversi' => 1],
                ['satuan_id' => $strip->id, 'konversi' => 10],
                ['satuan_id' => $this->box->id, 'konversi' => 100],
            ],
            'selected_satuan_id' => $this->box->id,
        ])->assertCreated()->assertJsonCount(3, 'data')->assertJsonPath('created_count', 3)
            ->assertJsonPath('data.0.satuan.nama', 'Box')->assertJsonPath('data.0.is_default', 1);
        $this->assertSame($response->json('data.0.id'), $response->json('selected_id'));
        $this->assertSame(1, KonversiSatuanModel::where('obat_id', $this->medicine->id)->where('is_default', true)->count());
        $this->assertDatabaseHas('obat_satuan_conversions', ['obat_id' => $this->medicine->id, 'satuan_id' => $strip->id, 'konversi' => 10]);
        $this->getJson(route('pembelian.getKonversiSatuan', ['obat_ids' => [$this->medicine->id]]))
            ->assertOk()->assertJsonCount(3, (string) $this->medicine->id);
    }

    public function test_modal_can_add_more_units_while_preserving_the_existing_default(): void
    {
        $existing = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->tablet->id, 'konversi' => 1, 'is_default' => true,
        ]);
        $strip = SatuansModel::create(['kode' => 'STR-INLINE', 'nama' => 'Strip', 'is_active' => true]);
        $payload = [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['satuan_id' => $strip->id, 'konversi' => 10],
                ['satuan_id' => $this->box->id, 'konversi' => 100],
            ],
            'selected_satuan_id' => $strip->id,
        ];
        $response = $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), $payload)
            ->assertCreated()->assertJsonCount(3, 'data')->assertJsonPath('created_count', 2);
        $this->assertDatabaseHas('obat_satuan_conversions', ['id' => $existing->id, 'konversi' => 1, 'is_default' => true]);
        $this->assertDatabaseHas('obat_satuan_conversions', ['id' => $response->json('selected_id'), 'satuan_id' => $strip->id, 'is_default' => false]);
        $this->postJson(route('pembelian.storeKonversiSatuan'), $payload)->assertOk()
            ->assertJsonPath('created_count', 0)->assertJsonPath('selected_id', $response->json('selected_id'));
        $this->assertDatabaseCount('obat_satuan_conversions', 3);
    }

    public function test_duplicate_units_in_the_modal_are_rejected_without_partial_writes(): void
    {
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['satuan_id' => $this->box->id, 'konversi' => 100],
                ['satuan_id' => $this->box->id, 'konversi' => 200],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions.1.satuan_id');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public function test_invalid_stock_factor_in_a_later_modal_row_prevents_every_write(): void
    {
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['satuan_id' => $this->box->id, 'konversi' => 100],
                ['satuan_id' => $this->tablet->id, 'konversi' => 2],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions.1.konversi');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public function test_conflicting_existing_unit_prevents_partial_modal_writes_and_preserves_the_old_factor(): void
    {
        KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->box->id, 'konversi' => 100, 'is_default' => true,
        ]);
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['satuan_id' => $this->tablet->id, 'konversi' => 1],
                ['satuan_id' => $this->box->id, 'konversi' => 200],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions.1.satuan_id');
        $this->assertDatabaseCount('obat_satuan_conversions', 1);
        $this->assertDatabaseHas('obat_satuan_conversions', ['satuan_id' => $this->box->id, 'konversi' => 100, 'is_default' => true]);
    }

    public function test_purchase_unit_must_be_one_of_the_modal_rows(): void
    {
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [['satuan_id' => $this->box->id, 'konversi' => 100]],
            'selected_satuan_id' => $this->tablet->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('selected_satuan_id');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public function test_modal_can_edit_an_existing_factor_and_add_a_unit_without_replacing_its_identity_or_default(): void
    {
        $existing = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->box->id,
            'konversi' => 100, 'is_default' => true,
        ]);
        $response = $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['id' => $existing->id, 'satuan_id' => $this->box->id, 'konversi' => 200],
                ['satuan_id' => $this->tablet->id, 'konversi' => 1],
            ],
            'selected_satuan_id' => $this->box->id,
        ])->assertCreated()->assertJsonPath('updated_count', 1)->assertJsonPath('created_count', 1)
            ->assertJsonPath('selected_id', $existing->id)->assertJsonCount(2, 'data');

        $this->assertSame(200, (int) $existing->fresh()->konversi);
        $this->assertSame(1, (int) $existing->fresh()->is_default);
        $this->assertSame($existing->id, $response->json('data.0.id'));
        $this->getJson(route('pembelian.getKonversiSatuan', ['obat_ids' => [$this->medicine->id]]))
            ->assertOk()->assertJsonPath($this->medicine->id.'.0.konversi', 200)
            ->assertJsonPath($this->medicine->id.'.0.is_used', 0);
    }

    public function test_modal_can_change_an_unused_purchase_unit_while_retaining_the_conversion_id(): void
    {
        $existing = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->box->id,
            'konversi' => 100, 'is_default' => true,
        ]);
        $strip = SatuansModel::create(['kode' => 'STR-EDIT', 'nama' => 'Strip', 'is_active' => true]);

        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [['id' => $existing->id, 'satuan_id' => $strip->id, 'konversi' => 10]],
            'selected_satuan_id' => $strip->id,
        ])->assertOk()->assertJsonPath('selected_id', $existing->id)
            ->assertJsonPath('updated_count', 1)->assertJsonPath('created_count', 0);

        $this->assertDatabaseCount('obat_satuan_conversions', 1);
        $this->assertDatabaseHas('obat_satuan_conversions', [
            'id' => $existing->id, 'satuan_id' => $strip->id, 'konversi' => 10, 'is_default' => true,
        ]);
    }

    public function test_modal_rejects_another_medicines_conversion_without_creating_partial_rows(): void
    {
        $other = MasterObatModel::create(['kode_obat' => 'OTHER-EDIT', 'nama_obat' => 'Obat Lain', 'satuan_id' => $this->tablet->id]);
        $existing = KonversiSatuanModel::create([
            'obat_id' => $other->id, 'satuan_id' => $this->box->id, 'konversi' => 100,
        ]);

        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['satuan_id' => $this->tablet->id, 'konversi' => 1],
                ['id' => $existing->id, 'satuan_id' => $this->box->id, 'konversi' => 200],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions.1.id');

        $this->assertDatabaseCount('obat_satuan_conversions', 1);
        $this->assertSame(100, (int) $existing->fresh()->konversi);
    }

    public function test_a_new_conversion_can_reuse_a_unit_freed_by_an_edit_regardless_of_row_order(): void
    {
        $existing = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->box->id,
            'konversi' => 100, 'is_default' => true,
        ]);
        $strip = SatuansModel::create(['kode' => 'STR-FREED', 'nama' => 'Strip', 'is_active' => true]);
        $payload = [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['satuan_id' => $this->box->id, 'konversi' => 200],
                ['id' => $existing->id, 'satuan_id' => $strip->id, 'konversi' => 10],
            ],
            'selected_satuan_id' => $this->box->id,
        ];
        $response = $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), $payload)
            ->assertCreated()->assertJsonPath('created_count', 1)->assertJsonPath('updated_count', 1);
        $this->assertNotEquals($existing->id, $response->json('selected_id'));
        $this->assertDatabaseHas('obat_satuan_conversions', ['id' => $existing->id, 'satuan_id' => $strip->id, 'is_default' => true]);
        $this->postJson(route('pembelian.storeKonversiSatuan'), $payload)->assertOk()
            ->assertJsonPath('selected_id', $response->json('selected_id'))
            ->assertJsonPath('created_count', 0)->assertJsonPath('updated_count', 0);
        $this->assertDatabaseCount('obat_satuan_conversions', 2);
    }

    public function test_modal_rejects_changing_a_unit_to_an_existing_conversion_omitted_from_the_payload(): void
    {
        $existing = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->box->id, 'konversi' => 100,
        ]);
        KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->tablet->id, 'konversi' => 1,
        ]);

        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [['id' => $existing->id, 'satuan_id' => $this->tablet->id, 'konversi' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions.0.satuan_id');

        $this->assertSame($this->box->id, $existing->fresh()->satuan_id);
        $this->assertDatabaseCount('obat_satuan_conversions', 2);
    }

    public function test_modal_can_preserve_an_inactive_existing_unit_but_cannot_add_an_inactive_unit(): void
    {
        $this->box->update(['is_active' => false]);
        $existing = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->box->id, 'konversi' => 100, 'is_default' => true,
        ]);
        $this->actingAs($this->user)->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['id' => $existing->id, 'satuan_id' => $this->box->id, 'konversi' => 100],
                ['satuan_id' => $this->tablet->id, 'konversi' => 1],
            ],
            'selected_satuan_id' => $this->tablet->id,
        ])->assertCreated()->assertJsonPath('updated_count', 0)->assertJsonPath('created_count', 1);

        $inactive = SatuansModel::create(['kode' => 'OLD-EDIT', 'nama' => 'Lama', 'is_active' => false]);
        $this->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [['satuan_id' => $inactive->id, 'konversi' => 50]],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions.0.satuan_id');
        $this->assertDatabaseCount('obat_satuan_conversions', 2);
    }

    public function test_editing_a_conversion_already_used_by_a_po_rolls_back_every_modal_change(): void
    {
        $existing = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $this->box->id,
            'konversi' => 100, 'is_default' => true,
        ]);
        $branch = BranchModel::create(['code' => 'LOCK-EDIT', 'name' => 'Cabang', 'is_active' => true]);
        $this->user->branches()->attach($branch->id);
        $distributor = DistributorModel::create(['kode' => 'DST-EDIT', 'nama' => 'Distributor', 'is_active' => true]);
        $this->actingAs($this->user)->postJson(route('pembelian.store'), [
            'no_po' => 'PO-LOCK-EDIT', 'distributor_id' => $distributor->id, 'tanggal' => '14-10-2026',
            'total_estimasi' => 100000, 'obat_id' => [$this->medicine->id], 'qty' => [1],
            'harga_estimasi' => [100000], 'diskon_1' => [0], 'diskon_2' => [0], 'diskon_3' => [0],
            'ppn' => [0], 'subtotal' => [100000], 'satuan_id' => [$existing->id], 'save_as_draft' => true,
        ])->assertOk();

        $this->getJson(route('pembelian.getKonversiSatuan', ['obat_id' => $this->medicine->id]))
            ->assertOk()->assertJsonPath('0.is_used', 1);
        $this->getJson(route('pembelian.getKonversiSatuan', ['obat_ids' => [$this->medicine->id]]))
            ->assertOk()->assertJsonPath($this->medicine->id.'.0.is_used', 1);
        $this->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [
                ['satuan_id' => $this->tablet->id, 'konversi' => 1],
                ['id' => $existing->id, 'satuan_id' => $this->box->id, 'konversi' => 200],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions.1.konversi');

        $this->assertDatabaseCount('obat_satuan_conversions', 1);
        $this->assertSame(100, (int) $existing->fresh()->konversi);
        $this->assertDatabaseHas('purchase_order_details', ['satuan_konversi' => $existing->id, 'subtotal' => 100000]);
        $this->postJson(route('pembelian.storeKonversiSatuan'), [
            'obat_id' => $this->medicine->id,
            'conversions' => [['id' => $existing->id, 'satuan_id' => $this->box->id, 'konversi' => 100]],
        ])->assertOk()->assertJsonPath('updated_count', 0)->assertJsonPath('selected_id', $existing->id);
    }

    private function payload(): array
    {
        return ['obat_id' => $this->medicine->id, 'satuan_id' => $this->box->id, 'konversi' => 100];
    }
}
