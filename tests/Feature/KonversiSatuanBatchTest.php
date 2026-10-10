<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class KonversiSatuanBatchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private SatuansModel $tablet;
    private SatuansModel $bottle;
    private SatuansModel $box;
    private MasterObatModel $first;
    private MasterObatModel $second;
    private MasterObatModel $third;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->tablet = SatuansModel::create(['kode' => 'TAB', 'nama' => 'Tablet']);
        $this->bottle = SatuansModel::create(['kode' => 'BTL', 'nama' => 'Botol']);
        $this->box = SatuansModel::create(['kode' => 'BOX', 'nama' => 'Box']);
        $this->first = $this->medicine('A', $this->tablet);
        $this->second = $this->medicine('B', $this->tablet);
        $this->third = $this->medicine('C', $this->bottle);
        $this->actingAs($this->user);
    }

    public function test_stock_unit_selection_adds_multiple_conversions_to_matching_medicines(): void
    {
        $strip = SatuansModel::create(['kode' => 'STR', 'nama' => 'Strip']);
        $selection = ['scope' => 'all', 'satuan_stok_ids' => [$this->tablet->id]];
        $targets = $this->previewIds($selection);
        $this->assertEqualsCanonicalizing([$this->first->id, $this->second->id], $targets);

        $this->save($selection, $targets, [
            ['satuan_id' => $this->box->id, 'konversi' => 100, 'is_default' => 1],
            ['satuan_id' => $strip->id, 'konversi' => 10, 'is_default' => 0],
        ])->assertOk()->assertJsonPath('data.added', 4)->assertJsonPath('data.updated_medicines', 2);

        foreach ([$this->first, $this->second] as $medicine) {
            $this->assertDatabaseHas('obat_satuan_conversions', ['obat_id' => $medicine->id, 'satuan_id' => $this->box->id, 'konversi' => 100, 'is_default' => 1]);
            $this->assertDatabaseHas('obat_satuan_conversions', ['obat_id' => $medicine->id, 'satuan_id' => $strip->id, 'konversi' => 10, 'is_default' => 0]);
        }
        $this->assertDatabaseMissing('obat_satuan_conversions', ['obat_id' => $this->third->id]);
    }

    public function test_medicine_selection_works_without_stock_unit_selection(): void
    {
        $selection = ['scope' => 'all', 'obat_ids' => [$this->first->id, $this->third->id]];
        $targets = $this->previewIds($selection);
        $this->assertEqualsCanonicalizing([$this->first->id, $this->third->id], $targets);
        $this->save($selection, $targets)->assertOk()->assertJsonPath('data.added', 2);
        $this->assertDatabaseMissing('obat_satuan_conversions', ['obat_id' => $this->second->id]);
    }

    public function test_combining_units_and_medicines_uses_the_intersection(): void
    {
        $selection = ['scope' => 'all', 'satuan_stok_ids' => [$this->tablet->id], 'obat_ids' => [$this->first->id, $this->third->id]];
        $targets = $this->previewIds($selection);
        $this->assertSame([$this->first->id], $targets);
        $this->save($selection, $targets)->assertOk()->assertJsonPath('data.added', 1);
        $this->assertDatabaseCount('obat_satuan_conversions', 1);
    }

    public function test_existing_factors_are_skipped_and_only_one_new_default_is_set(): void
    {
        $existing = KonversiSatuanModel::create(['obat_id' => $this->first->id, 'satuan_id' => $this->box->id, 'konversi' => 20, 'is_default' => 1]);
        $strip = SatuansModel::create(['kode' => 'STR', 'nama' => 'Strip']);
        $order = $this->order($this->branch('OWN'), [$this->first]);
        $detail = $order->details()->first();
        $detail->update(['satuan_konversi' => $existing->id]);
        $selection = ['scope' => 'all', 'obat_ids' => [$this->first->id, $this->second->id]];

        $this->save($selection, $this->previewIds($selection), [
            ['satuan_id' => $this->box->id, 'konversi' => 100, 'is_default' => 0],
            ['satuan_id' => $strip->id, 'konversi' => 10, 'is_default' => 1],
        ])->assertOk()->assertJsonPath('data.added', 3)->assertJsonPath('data.skipped', 1);

        $this->assertSame(20, (int) $existing->fresh()->konversi);
        $this->assertSame($existing->id, $detail->fresh()->satuan_konversi);
        foreach ([$this->first, $this->second] as $medicine) {
            $this->assertSame(1, $medicine->konversiSatuan()->where('is_default', 1)->count());
            $this->assertDatabaseHas('obat_satuan_conversions', ['obat_id' => $medicine->id, 'satuan_id' => $strip->id, 'is_default' => 1]);
        }

        $this->save($selection, $this->previewIds($selection))->assertOk()->assertJsonPath('data.added', 0)->assertJsonPath('data.skipped', 2);
        $this->assertSame(20, (int) $existing->fresh()->konversi);
        $this->assertDatabaseHas('obat_satuan_conversions', ['obat_id' => $this->first->id, 'satuan_id' => $this->box->id, 'is_default' => 1]);
    }

    public function test_po_filter_shows_only_missing_medicines_and_accessible_order_references(): void
    {
        $own = $this->branch('OWN');
        $other = $this->branch('OTHER', false);
        $this->first->update(['is_active' => false]);
        $ownOrder = $this->order($own, [$this->first, $this->first, $this->second]);
        $this->order($other, [$this->first, $this->third]);
        $baseUnit = KonversiSatuanModel::create(['obat_id' => $this->second->id, 'satuan_id' => $this->tablet->id, 'konversi' => 1]);
        $ownOrder->details()->where('obat_id', $this->second->id)->update(['satuan_konversi' => $baseUnit->id]);

        $response = $this->getJson(route('konversiSatuanObat.table', ['status' => 'po_without']))
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->first->id)
            ->assertJsonPath('data.0.po_count', 1)
            ->assertJsonPath('data.0.purchase_orders.0', $ownOrder->no_po)
            ->assertJsonPath('summary.po_belum_konversi', 1);
        $this->assertStringContainsString($ownOrder->no_po, $response->json('data.0.search_text'));
        $this->assertSame([$this->first->id], $this->previewIds(['scope' => 'po_without']));

        $this->actingAs(User::factory()->create());
        $this->getJson(route('konversiSatuanObat.table', ['status' => 'po_without']))
            ->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('summary.po_belum_konversi', 0);
        $this->assertSame([], $this->previewIds(['scope' => 'po_without']));
    }

    public function test_po_missing_batch_can_be_saved_and_removes_medicines_from_the_worklist(): void
    {
        $own = $this->branch('OWN');
        $other = $this->branch('OTHER', false);
        $order = $this->order($own, [$this->first, $this->second]);
        $this->order($other, [$this->third]);
        $selection = ['scope' => 'po_without'];
        $this->save($selection, $this->previewIds($selection))->assertOk()->assertJsonPath('data.added', 2)
            ->assertJsonPath('data.po_sync.updated_items', 2)->assertJsonPath('data.po_sync.updated_orders', 1);
        $this->getJson(route('konversiSatuanObat.table', ['status' => 'po_without']))
            ->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('summary.po_belum_konversi', 0);
        $this->assertDatabaseMissing('obat_satuan_conversions', ['obat_id' => $this->third->id]);
        $this->assertSame('approved', $order->fresh()->status);
        $this->assertSame(2, $order->details()->whereNotNull('satuan_konversi')->count());
        $this->assertSame(2, $order->details()->count());
    }

    public function test_single_editor_can_complete_a_po_medicine_without_conversion(): void
    {
        $this->order($this->branch('OWN'), [$this->first]);
        $this->putJson(route('konversiSatuanObat.sync', $this->first->id), [
            'satuan_id' => [$this->box->id], 'konversi' => [10], 'is_default' => [1],
        ])->assertOk();
        $this->getJson(route('konversiSatuanObat.table', ['status' => 'po_without']))
            ->assertOk()->assertJsonCount(0, 'data');
        $this->getJson(route('pembelian.getKonversiSatuan', ['obat_ids' => [$this->first->id]]))
            ->assertOk()->assertJsonPath($this->first->id.'.0.konversi', 10);
    }

    public function test_changed_target_set_is_rejected_without_partial_writes(): void
    {
        $selection = ['scope' => 'without', 'satuan_stok_ids' => [$this->tablet->id]];
        $targets = $this->previewIds($selection);
        $existing = KonversiSatuanModel::create(['obat_id' => $this->second->id, 'satuan_id' => $this->box->id, 'konversi' => 5]);
        $this->save($selection, $targets)->assertUnprocessable()->assertJsonValidationErrors('target_ids');
        $this->assertDatabaseCount('obat_satuan_conversions', 1);
        $this->assertSame(5, (int) $existing->fresh()->konversi);
    }

    public function test_empty_and_invalid_selectors_cannot_apply_to_all_medicines(): void
    {
        $this->getJson(route('konversiSatuanObat.batchPreview', ['scope' => 'all']))
            ->assertUnprocessable()->assertJsonValidationErrors('obat_ids');
        $this->save(['scope' => 'all'], [$this->first->id])->assertUnprocessable()->assertJsonValidationErrors('obat_ids');
        $this->getJson(route('konversiSatuanObat.batchPreview', ['scope' => 'all', 'obat_ids' => [999999]]))
            ->assertUnprocessable()->assertJsonValidationErrors('obat_ids.0');
        $this->getJson(route('konversiSatuanObat.batchPreview', ['scope' => 'all', 'satuan_stok_ids' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors('satuan_stok_ids');
        $selection = ['scope' => 'all', 'satuan_stok_ids' => [$this->tablet->id], 'obat_ids' => [$this->third->id]];
        $this->assertSame([], $this->previewIds($selection));
        $this->save($selection, [$this->third->id])->assertUnprocessable()->assertJsonValidationErrors('target_ids');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public function test_invalid_conversion_rows_are_rejected_without_writes(): void
    {
        $selection = ['scope' => 'all', 'obat_ids' => [$this->first->id]];
        $targets = $this->previewIds($selection);
        foreach ([0, -1, 1.5, 2147483648] as $amount) {
            $this->save($selection, $targets, [['satuan_id' => $this->box->id, 'konversi' => $amount]])
                ->assertUnprocessable()->assertJsonValidationErrors('conversions.0.konversi');
        }
        $this->save($selection, $targets, [
            ['satuan_id' => $this->box->id, 'konversi' => 10],
            ['satuan_id' => $this->box->id, 'konversi' => 20],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions.0.satuan_id');
        $this->save($selection, $targets, [
            ['satuan_id' => $this->box->id, 'konversi' => 10, 'is_default' => 1],
            ['satuan_id' => $this->tablet->id, 'konversi' => 1, 'is_default' => 1],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
    }

    public function test_stock_unit_factor_must_be_one_for_every_target_before_any_write(): void
    {
        $selection = ['scope' => 'all', 'obat_ids' => [$this->first->id, $this->third->id]];
        $targets = $this->previewIds($selection);
        $this->save($selection, $targets, [
            ['satuan_id' => $this->box->id, 'konversi' => 10],
            ['satuan_id' => $this->bottle->id, 'konversi' => 10],
        ])->assertUnprocessable()->assertJsonValidationErrors('conversions');
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
        $this->save($selection, $targets, [['satuan_id' => $this->bottle->id, 'konversi' => 1]])->assertOk();
        $this->assertDatabaseHas('obat_satuan_conversions', ['obat_id' => $this->third->id, 'satuan_id' => $this->bottle->id, 'konversi' => 1]);
    }

    public function test_page_renders_batch_form_and_po_filter(): void
    {
        $this->get(route('konversiSatuanObat.konversiSatuanObat'))->assertOk()
            ->assertSee('Atur Konversi Batch')->assertSee('batchStockUnits', false)
            ->assertSee('batchMedicines', false)->assertSee('po_without', false)
            ->assertSee(route('konversiSatuanObat.batchStore'), false);
    }

    public function test_po_unit_update_preserves_document_values_and_receiving_uses_the_new_unit(): void
    {
        $order = $this->order($this->branch('OWN'), [$this->first]);
        $detail = $order->details()->first();
        $detail->update([
            'qty' => 2.5, 'harga_estimasi' => 12000,
            'diskon_1' => 10, 'diskon_2' => 5, 'diskon_3' => 2, 'ppn' => 11, 'subtotal' => 27902.7,
        ]);
        $order->update(['total_estimasi' => 29702.7, 'biaya_asuransi' => 500, 'biaya_pengiriman' => 1300, 'approved_by' => $this->user->id, 'catatan' => 'Jangan ubah nilai PO']);
        $originalHeader = $order->refresh()->getRawOriginal();
        $originalDetail = $detail->getRawOriginal();
        $selection = ['scope' => 'po_without'];

        $this->save($selection, $this->previewIds($selection))->assertOk()->assertJsonPath('data.po_sync.updated_items', 1);

        $conversion = KonversiSatuanModel::where('obat_id', $this->first->id)->firstOrFail();
        $this->assertSame($conversion->id, $detail->fresh()->satuan_konversi);
        $this->assertSame($originalHeader, $order->fresh()->getRawOriginal());
        $this->assertSame(
            array_diff_key($originalDetail, array_flip(['satuan_konversi', 'updated_at'])),
            array_diff_key($detail->fresh()->getRawOriginal(), array_flip(['satuan_konversi', 'updated_at']))
        );
        $this->getJson(route('penerimaan.purchaseOrderDetail', $order->id))->assertOk()
            ->assertJsonPath('details.0.satuan', 'Box')->assertJsonPath('details.0.konversi', 10)
            ->assertJsonPath('details.0.qty_po', 2.5)->assertJsonPath('details.0.qty_po_stok', 25)
            ->assertJsonPath('details.0.harga_estimasi', 12000)->assertJsonPath('details.0.harga_estimasi_stok', 1200);
        $this->getJson(route('pembelian.table', ['medicine_id' => $this->first->id]))->assertOk()
            ->assertJsonPath('data.0.matched_medicines.0.satuan', 'Box');
    }

    public function test_single_editor_updates_missing_units_on_draft_waiting_and_approved_orders(): void
    {
        $branch = $this->branch('OWN');
        $orders = [];
        foreach (['draft', 'waiting_approval', 'approved'] as $status) {
            $order = $this->order($branch, [$this->first, $this->first]);
            $order->update(['status' => $status]);
            $orders[] = $order;
        }
        $this->putJson(route('konversiSatuanObat.sync', $this->first->id), [
            'satuan_id' => [$this->box->id], 'konversi' => [10], 'is_default' => [0],
        ])->assertOk()->assertJsonPath('data.po_sync.updated_items', 6)->assertJsonPath('data.po_sync.updated_orders', 3);

        foreach ($orders as $order) {
            $this->assertSame($order->status, $order->fresh()->status);
            $this->assertSame(2, $order->details()->whereNotNull('satuan_konversi')->count());
            $this->assertSame(4.0, (float) $order->details()->sum('qty'));
        }
    }

    public function test_orders_with_draft_posted_or_cancelled_receipts_are_protected(): void
    {
        $branch = $this->branch('OWN');
        $orders = [];
        $receiptDetails = [];
        foreach (['draft', 'posted', 'cancelled'] as $status) {
            $order = $this->order($branch, [$this->first, $this->second]);
            $receipt = $this->receipt($order, $status);
            $receiptDetail = PenerimaanBarangDetailModel::create([
                'penerimaan_barang_id' => $receipt->id, 'purchase_order_detail_id' => $order->details()->first()->id,
                'obat_id' => $this->first->id, 'qty_po' => 2, 'qty_diterima' => 1, 'qty_diterima_stok' => 1,
                'konversi_satuan' => 1, 'satuan_beli' => 'Tablet', 'satuan_stok' => 'Tablet',
                'harga_beli' => 1000, 'harga_beli_stok' => 1000, 'no_batch' => 'OLD-'.$status,
            ]);
            $orders[] = [$order, $order->refresh()->getRawOriginal()];
            $receiptDetails[] = [$receiptDetail, $receiptDetail->refresh()->getRawOriginal()];
        }
        $selection = ['scope' => 'all', 'obat_ids' => [$this->first->id, $this->second->id]];
        $this->save($selection, $this->previewIds($selection))->assertOk()
            ->assertJsonPath('data.po_sync.updated_items', 0)->assertJsonPath('data.po_sync.protected_items', 6);

        foreach ($orders as [$order, $original]) {
            $this->assertSame(0, $order->details()->whereNotNull('satuan_konversi')->count());
            $this->assertSame($original, $order->fresh()->getRawOriginal());
        }
        foreach ($receiptDetails as [$detail, $original]) {
            $this->assertSame($original, $detail->fresh()->getRawOriginal());
        }
        $this->getJson(route('konversiSatuanObat.table', ['status' => 'po_without']))->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_closed_orders_and_orders_in_other_branches_are_not_changed(): void
    {
        $own = $this->branch('OWN');
        $other = $this->branch('OTHER', false);
        $openOrder = $this->order($own, [$this->first]);
        $rejected = $this->order($own, [$this->first]);
        $rejected->update(['status' => 'rejected']);
        $completed = $this->order($own, [$this->first]);
        DB::statement('PRAGMA ignore_check_constraints = ON');
        $completed->update(['status' => 'selesai']);
        DB::statement('PRAGMA ignore_check_constraints = OFF');
        $otherOrder = $this->order($other, [$this->first]);
        $selection = ['scope' => 'all', 'obat_ids' => [$this->first->id]];
        $this->save($selection, $this->previewIds($selection))->assertOk()
            ->assertJsonPath('data.po_sync.updated_items', 1)->assertJsonPath('data.po_sync.protected_items', 2);

        $this->assertNotNull($openOrder->details()->first()->satuan_konversi);
        foreach ([$rejected, $completed, $otherOrder] as $order) {
            $this->assertNull($order->details()->first()->satuan_konversi);
            $this->assertSame($order->status, $order->fresh()->status);
        }
    }

    public function test_existing_po_unit_is_preserved_and_its_conversion_cannot_be_changed_or_deleted(): void
    {
        $box = KonversiSatuanModel::create(['obat_id' => $this->first->id, 'satuan_id' => $this->box->id, 'konversi' => 10, 'is_default' => 1]);
        $order = $this->order($this->branch('OWN'), [$this->first]);
        $detail = $order->details()->first();
        $detail->update(['satuan_konversi' => $box->id, 'harga_estimasi' => 12000, 'subtotal' => 24000]);
        $originalDetail = $detail->getRawOriginal();
        $strip = SatuansModel::create(['kode' => 'STR', 'nama' => 'Strip']);
        $this->putJson(route('konversiSatuanObat.sync', $this->first->id), [
            'conversion_id' => [$box->id, null], 'satuan_id' => [$this->box->id, $strip->id],
            'konversi' => [10, 5], 'is_default' => [0, 1],
        ])->assertOk()->assertJsonPath('data.po_sync.updated_items', 0);
        $this->assertSame($originalDetail, $detail->fresh()->getRawOriginal());
        $this->assertSame(10, (int) $box->fresh()->konversi);

        $this->putJson(route('konversiSatuanObat.sync', $this->first->id), [
            'conversion_id' => [$box->id], 'satuan_id' => [$this->box->id], 'konversi' => [100], 'is_default' => [1],
        ])->assertUnprocessable()->assertJsonValidationErrors('konversi');
        $this->putJson(route('konversiSatuanObat.update', $box->id), [
            'obat_id' => $this->first->id, 'satuan_id' => $this->box->id, 'konversi' => 100, 'is_default' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('konversi');
        $this->deleteJson(route('konversiSatuanObat.destroy', $box->id))
            ->assertUnprocessable()->assertJsonValidationErrors('konversi');
        $this->assertSame(10, (int) $box->fresh()->konversi);
        $this->assertSame($originalDetail, $detail->fresh()->getRawOriginal());
        $this->assertDatabaseHas('obat_satuan_conversions', ['obat_id' => $this->first->id, 'satuan_id' => $strip->id, 'is_default' => 1]);
    }

    public function test_ambiguous_units_remain_in_po_worklist_until_a_default_is_chosen(): void
    {
        $order = $this->order($this->branch('OWN'), [$this->first]);
        $strip = SatuansModel::create(['kode' => 'STR', 'nama' => 'Strip']);
        $selection = ['scope' => 'po_without'];
        $this->save($selection, $this->previewIds($selection), [
            ['satuan_id' => $this->box->id, 'konversi' => 10, 'is_default' => 0],
            ['satuan_id' => $strip->id, 'konversi' => 5, 'is_default' => 0],
        ])->assertOk()->assertJsonPath('data.po_sync.updated_items', 0)->assertJsonPath('data.po_sync.ambiguous_items', 1);
        $this->assertNull($order->details()->first()->satuan_konversi);
        $this->getJson(route('konversiSatuanObat.table', ['status' => 'po_without']))->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.has_konversi', true)
            ->assertJsonPath('data.0.po_missing_unit_count', 1)->assertJsonPath('summary.po_belum_konversi', 1);

        $this->save($selection, $this->previewIds($selection))->assertOk()->assertJsonPath('data.added', 0)
            ->assertJsonPath('data.po_sync.updated_items', 1);
        $this->assertSame($this->box->id, $order->details()->first()->satuanKonversi->satuan_id);
        $this->getJson(route('konversiSatuanObat.table', ['status' => 'po_without']))->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_po_write_failure_rolls_back_conversions_and_preceding_po_updates(): void
    {
        $order = $this->order($this->branch('OWN'), [$this->first, $this->second]);
        $lastDetail = $order->details()->orderByDesc('id')->first();
        DB::unprepared('CREATE TRIGGER reject_po_unit_update BEFORE UPDATE ON purchase_order_details WHEN NEW.id = '.(int) $lastDetail->id.' AND NEW.satuan_konversi IS NOT NULL BEGIN SELECT RAISE(ABORT, \'Simulated PO write failure\'); END');
        $selection = ['scope' => 'po_without'];
        $this->save($selection, $this->previewIds($selection))->assertStatus(500);
        $this->assertDatabaseCount('obat_satuan_conversions', 0);
        $this->assertSame(0, $order->details()->whereNotNull('satuan_konversi')->count());
        $this->assertSame('approved', $order->fresh()->status);
    }

    public function test_legacy_store_also_repairs_missing_po_units(): void
    {
        $order = $this->order($this->branch('OWN'), [$this->first]);
        $this->postJson(route('konversiSatuanObat.store'), [
            'obat_id' => [$this->first->id], 'satuan_id' => [$this->box->id], 'konversi' => [10], 'is_default' => [1],
        ])->assertOk()->assertJsonPath('po_sync.updated_items', 1);
        $this->assertSame($this->box->id, $order->details()->first()->satuanKonversi->satuan_id);
    }

    public function test_setting_an_existing_default_conversion_also_repairs_missing_po_units(): void
    {
        $order = $this->order($this->branch('OWN'), [$this->first]);
        $box = KonversiSatuanModel::create(['obat_id' => $this->first->id, 'satuan_id' => $this->box->id, 'konversi' => 10, 'is_default' => 0]);
        $base = KonversiSatuanModel::create(['obat_id' => $this->first->id, 'satuan_id' => $this->tablet->id, 'konversi' => 1, 'is_default' => 0]);
        $this->putJson(route('konversiSatuanObat.update', $box->id), [
            'obat_id' => $this->first->id, 'satuan_id' => $this->box->id, 'konversi' => 10, 'is_default' => 1,
        ])->assertOk()->assertJsonPath('po_sync.updated_items', 1);
        $this->assertSame($box->id, $order->details()->first()->satuan_konversi);
        $this->assertSame(0, (int) $base->fresh()->is_default);
    }

    public function test_excel_import_also_repairs_missing_po_units(): void
    {
        $order = $this->order($this->branch('OWN'), [$this->first]);
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['Obat', 'Satuan', 'Konversi'], [$this->first->kode_obat, 'Box', 10],
        ]);
        $temporaryFile = tempnam(sys_get_temp_dir(), 'konversi-po-');
        try {
            (new Xlsx($spreadsheet))->save($temporaryFile);
            $upload = UploadedFile::fake()->createWithContent('konversi.xlsx', file_get_contents($temporaryFile));
            $this->postJson(route('konversiSatuanObat.import'), ['file' => $upload])->assertOk()
                ->assertJsonPath('success', true)->assertJsonPath('added', 1)->assertJsonPath('po_sync.updated_items', 1);
            $this->assertSame($this->box->id, $order->details()->first()->satuanKonversi->satuan_id);
        } finally {
            $spreadsheet->disconnectWorksheets();
            unlink($temporaryFile);
        }
    }

    private function receipt(PembelianModel $order, string $status): PenerimaanBarangModel
    {
        return PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-'.(PenerimaanBarangModel::count() + 1),
            'purchase_order_id' => $order->id, 'distributor_id' => $order->distributor_id,
            'nomor_faktur' => 'INV-'.$order->id, 'tanggal_penerimaan' => '2026-10-10',
            'status' => $status, 'created_by' => $this->user->id,
        ]);
    }

    private function medicine(string $code, SatuansModel $unit): MasterObatModel
    {
        return MasterObatModel::create(['kode_obat' => $code, 'nama_obat' => 'Obat '.$code, 'satuan_id' => $unit->id, 'is_active' => true]);
    }

    private function branch(string $code, bool $assigned = true): BranchModel
    {
        $branch = BranchModel::create(['code' => $code, 'name' => 'Cabang '.$code, 'is_active' => true]);
        if ($assigned) $this->user->branches()->attach($branch);

        return $branch;
    }

    private function order(BranchModel $branch, array $medicines): PembelianModel
    {
        $distributor = DistributorModel::firstOrCreate(['kode' => 'DST'], ['nama' => 'Distributor Test']);
        $order = PembelianModel::create([
            'no_po' => 'PO-BATCH-'.(PembelianModel::count() + 1), 'branch_id' => $branch->id,
            'distributor_id' => $distributor->id, 'tanggal_po' => '2026-10-10',
            'status' => 'approved', 'created_by' => $this->user->id,
        ]);
        foreach ($medicines as $medicine) {
            PembelianDetailModel::create(['purchase_order_id' => $order->id, 'obat_id' => $medicine->id, 'qty' => 2]);
        }

        return $order;
    }

    private function previewIds(array $selection): array
    {
        $response = $this->getJson(route('konversiSatuanObat.batchPreview', $selection))->assertOk();

        return array_column($response->json('data'), 'id');
    }

    private function save(array $selection, array $targets, ?array $conversions = null)
    {
        return $this->postJson(route('konversiSatuanObat.batchStore'), [
            ...$selection, 'target_ids' => $targets,
            'conversions' => $conversions ?? [['satuan_id' => $this->box->id, 'konversi' => 10, 'is_default' => 1]],
        ]);
    }
}
