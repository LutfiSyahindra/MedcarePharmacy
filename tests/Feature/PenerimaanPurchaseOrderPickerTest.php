<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PenerimaanPurchaseOrderPickerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private DistributorModel $supplier;

    private MasterObatModel $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'CB-PO-PICKER', 'name' => 'Cabang Picker', 'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
        $this->supplier = DistributorModel::create([
            'kode' => 'DST-PO-PICKER', 'nama' => 'Supplier Picker', 'is_active' => true,
        ]);
        $unit = SatuansModel::create([
            'kode' => 'PCS-PICKER', 'nama' => 'PCS', 'is_active' => true,
        ]);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-PICKER', 'nama_obat' => 'Obat Picker',
            'satuan_id' => $unit->id, 'distributor_id' => $this->supplier->id, 'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    public function test_picker_lists_receivable_orders_with_remaining_quantity_including_drafts_and_partial_receipts(): void
    {
        $available = $this->createOrder('AVAILABLE');
        foreach (['draft', 'waiting_approval', 'rejected', 'selesai'] as $status) {
            $this->createOrder(strtoupper($status), $status);
        }
        $partial = $this->createOrder('PARTIAL', 'diterima_sebagian');
        $this->createReceipt($partial, 'posted', 4);
        $this->createOrder('EMPTY')->details()->delete();
        $receiving = $this->createOrder('RECEIVING');
        $this->createReceipt($receiving, 'draft', 2);
        $this->createReceipt($this->createOrder('FULL'), 'posted', 10);
        $this->createReceipt($available, 'cancelled', 10);

        $response = $this->getJson(route('penerimaan.approvedPo'))
            ->assertOk()
            ->assertJsonCount(3);
        $orders = collect($response->json())->keyBy('id');
        $this->assertSame('approved', $orders[$available->id]['status']);
        $this->assertSame('Supplier Picker', $orders[$available->id]['supplier']);
        $this->assertSame('Cabang Picker', $orders[$available->id]['branch']);
        $this->assertSame(1, $orders[$available->id]['item_count']);
        $this->assertSame(10, $orders[$available->id]['outstanding_qty']);
        $this->assertSame(99900, $orders[$available->id]['total_estimasi']);
        $this->assertSame(6, $orders[$partial->id]['outstanding_qty']);
        $this->assertSame(8, $orders[$receiving->id]['outstanding_qty']);
        $this->getJson(route('penerimaan.purchaseOrderDetail', $partial->id))
            ->assertOk()->assertJsonPath('can_create_receipt', true);
    }

    public function test_picker_and_purchase_details_respect_branch_access(): void
    {
        $otherBranch = BranchModel::create([
            'code' => 'CB-PICKER-OTHER', 'name' => 'Cabang Lain', 'is_active' => true,
        ]);
        $po = $this->createOrder('OTHER');
        $po->update(['branch_id' => $otherBranch->id]);

        $this->getJson(route('penerimaan.approvedPo'))->assertOk()->assertExactJson([]);
        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))->assertNotFound();
    }

    public function test_picker_includes_every_medicine_for_search_by_name_or_code(): void
    {
        $po = $this->createOrder('MEDICINES');
        $this->createReceipt($po, 'posted', 10);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'AMX-500', 'nama_obat' => 'Amoxicillin 500 mg',
            'satuan_id' => $this->medicine->satuan_id, 'distributor_id' => $this->supplier->id, 'is_active' => true,
        ]);
        $po->details()->create([
            'obat_id' => $medicine->id, 'qty' => 5, 'harga_estimasi' => 5000, 'subtotal' => 25000,
        ]);

        $response = $this->getJson(route('penerimaan.approvedPo'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $po->id)
            ->assertJsonPath('0.item_count', 2)
            ->assertJsonPath('0.outstanding_qty', 5)
            ->assertJsonCount(2, '0.medicines');
        $medicines = collect($response->json('0.medicines'))->keyBy('kode_obat');
        $this->assertSame('Obat Picker', $medicines['OBT-PICKER']['nama_obat']);
        $this->assertSame(0, $medicines['OBT-PICKER']['outstanding_qty']);
        $this->assertSame('Amoxicillin 500 mg', $medicines['AMX-500']['nama_obat']);
        $this->assertSame('PCS', $medicines['AMX-500']['satuan']);
        $this->assertSame(5, $medicines['AMX-500']['qty_po']);
        $this->assertSame(5, $medicines['AMX-500']['outstanding_qty']);
    }

    public function test_viewing_purchase_details_does_not_create_a_receipt(): void
    {
        $po = $this->createOrder('DETAIL');

        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()
            ->assertJsonPath('id', $po->id)
            ->assertJsonPath('can_create_receipt', true)
            ->assertJsonPath('catatan', 'Catatan pembelian picker')
            ->assertJsonPath('details.0.nama_obat', 'Obat Picker')
            ->assertJsonPath('details.0.satuan', 'PCS')
            ->assertJsonPath('details.0.qty_po', 10)
            ->assertJsonPath('details.0.outstanding_qty', 10)
            ->assertJsonPath('details.0.harga_estimasi', 10000)
            ->assertJsonPath('details.0.diskon_1', 10)
            ->assertJsonPath('details.0.ppn', 11);

        $this->assertDatabaseCount('penerimaan_barang', 0);
    }

    public function test_draft_quantity_reduces_availability_without_blocking_the_next_invoice(): void
    {
        $po = $this->createOrder('STALE');
        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()->assertJsonPath('can_create_receipt', true);
        $receipt = $this->createReceipt($po, 'draft', 2);

        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()
            ->assertJsonPath('can_create_receipt', true)
            ->assertJsonPath('details.0.outstanding_qty', 8);
        $this->getJson(route('penerimaan.approvedPo'))
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.outstanding_qty', 8);

        $receipt->update(['status' => 'cancelled']);
        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()->assertJsonPath('can_create_receipt', true)
            ->assertJsonPath('details.0.outstanding_qty', 10);
    }

    public function test_one_order_can_have_multiple_invoice_drafts_and_cannot_exceed_its_remaining_quantity(): void
    {
        $po = $this->createOrder('SPLIT');
        $first = $this->invoicePayload($po, 'FIRST', 3);
        $this->postJson(route('penerimaan.store'), $first)->assertOk();
        $firstReceipt = PenerimaanBarangModel::where('nomor_penerimaan', $first['nomor_penerimaan'])->firstOrFail();

        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'SECOND', 4))->assertOk();
        $this->assertDatabaseCount('penerimaan_barang', 2);
        $this->assertDatabaseHas('penerimaan_barang', ['nomor_faktur' => 'INV-SPLIT-FIRST', 'total_faktur' => 29970]);
        $this->assertDatabaseHas('penerimaan_barang', ['nomor_faktur' => 'INV-SPLIT-SECOND', 'total_faktur' => 39960]);
        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()->assertJsonPath('can_create_receipt', true)
            ->assertJsonPath('details.0.outstanding_qty', 3);
        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'OVER', 4))
            ->assertUnprocessable()->assertJsonValidationErrors('qty_diterima.0');
        $this->assertDatabaseCount('penerimaan_barang', 2);

        $this->getJson(route('penerimaan.edit', $firstReceipt->id))
            ->assertOk()->assertJsonPath('po_payload.details.0.outstanding_qty', 6);
        $first['qty_diterima'] = [4];
        $this->putJson(route('penerimaan.update', $firstReceipt->id), $first)->assertOk();
        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()->assertJsonPath('details.0.outstanding_qty', 2);

        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'THIRD', 2))->assertOk();
        $this->assertDatabaseCount('penerimaan_barang', 3);
        $this->assertSame(10.0, (float) $po->penerimaanBarang()->withSum('details', 'qty_diterima')->get()->sum('details_sum_qty_diterima'));
        $this->getJson(route('penerimaan.approvedPo'))->assertOk()->assertExactJson([]);
        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()->assertJsonPath('can_create_receipt', false);
    }

    public function test_posting_split_invoices_keeps_the_po_receivable_until_complete_and_cancellation_restores_quantity(): void
    {
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->user->assignRole('Admin');
        $po = $this->createOrder('POST-SPLIT');
        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'POST-FIRST', 4))->assertOk();
        $first = $po->penerimaanBarang()->firstOrFail();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }
        try {
            $this->putJson(route('penerimaan.post', $first->id), ['diskon_untuk' => 'pasien'])->assertOk();
            $this->assertSame('diterima_sebagian', $po->fresh()->status);
            $this->getJson(route('penerimaan.approvedPo'))
                ->assertOk()->assertJsonPath('0.id', $po->id)->assertJsonPath('0.outstanding_qty', 6);

            $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'POST-SECOND', 6))->assertOk();
            $second = $po->penerimaanBarang()->latest('id')->firstOrFail();
            $this->putJson(route('penerimaan.post', $second->id), ['diskon_untuk' => 'pasien'])->assertOk();
            $this->assertSame('selesai', $po->fresh()->status);
            $this->getJson(route('penerimaan.approvedPo'))->assertOk()->assertExactJson([]);

            $this->putJson(route('penerimaan.cancel', $second->id))->assertOk();
            $this->assertSame('diterima_sebagian', $po->fresh()->status);
            $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
                ->assertOk()->assertJsonPath('can_create_receipt', true)
                ->assertJsonPath('details.0.outstanding_qty', 6);
        } finally {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA ignore_check_constraints = OFF');
            }
        }
    }

    public function test_duplicate_item_rows_cannot_bypass_the_invoice_quantity_limit(): void
    {
        $po = $this->createOrder('DUPLICATE');
        $payload = $this->invoicePayload($po, 'DUPLICATE', 6);
        foreach (['purchase_order_detail_id', 'obat_id', 'qty_diterima', 'no_batch', 'expired_date', 'harga_beli', 'ppn'] as $field) {
            $payload[$field][] = $payload[$field][0];
        }

        $this->postJson(route('penerimaan.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('qty_diterima.1');
        $this->assertDatabaseCount('penerimaan_barang', 0);
    }

    public function test_fractional_invoice_quantities_can_exhaust_the_po_without_rounding_errors(): void
    {
        $po = $this->createOrder('FRACTIONAL');
        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'FRACTIONAL-FIRST', 9.8))->assertOk();
        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()->assertJsonPath('details.0.outstanding_qty', 0.2);
        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'FRACTIONAL-LAST', 0.2))->assertOk();
        $this->assertDatabaseCount('penerimaan_barang', 2);
        $this->getJson(route('penerimaan.approvedPo'))->assertOk()->assertExactJson([]);
    }

    public function test_saving_receipt_uses_the_selected_purchase_order_and_rejects_items_from_another_order(): void
    {
        $selected = $this->createOrder('SELECTED');
        $other = $this->createOrder('OTHER');
        $payload = [
            'nomor_penerimaan' => 'PB-PICKER-001',
            'purchase_order_id' => $selected->id,
            'nomor_faktur' => 'INV-PICKER-001',
            'tanggal_penerimaan' => '03-10-2026',
            'tanggal_faktur' => '03-10-2026',
            'purchase_order_detail_id' => [$other->details->first()->id],
            'obat_id' => [$this->medicine->id],
            'qty_diterima' => [3],
            'no_batch' => ['BATCH-PICKER'],
            'expired_date' => ['03-10-2027'],
            'harga_beli' => [10000],
            'ppn' => [11],
        ];
        $this->postJson(route('penerimaan.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('purchase_order_detail_id.0');
        $this->assertDatabaseCount('penerimaan_barang', 0);

        $payload['purchase_order_detail_id'] = [$selected->details->first()->id];
        $this->postJson(route('penerimaan.store'), $payload)->assertOk();
        $this->assertDatabaseHas('penerimaan_barang', [
            'nomor_penerimaan' => 'PB-PICKER-001', 'purchase_order_id' => $selected->id, 'status' => 'draft',
        ]);
        $this->assertDatabaseHas('penerimaan_barang_detail', [
            'purchase_order_detail_id' => $selected->details->first()->id, 'qty_diterima' => 3,
        ]);
        $this->assertDatabaseCount('penerimaan_barang', 1);
        $this->getJson(route('penerimaan.edit', PenerimaanBarangModel::firstOrFail()->id))
            ->assertOk()->assertJsonPath('header.purchase_order_id', $selected->id);
    }

    public function test_received_invoice_history_groups_active_receipts_and_ignores_cancelled_and_other_orders(): void
    {
        $po = $this->createOrder('HISTORY');
        $posted = $this->createReceipt($po, 'posted', 4);
        $posted->details()->firstOrFail()->update(['no_batch' => 'OLD-BATCH', 'expired_date' => '2027-10-03']);
        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'HISTORY-DRAFT', 3))->assertOk();
        $draft = $po->penerimaanBarang()->latest('id')->firstOrFail();
        $draft->details()->firstOrFail()->update(['satuan_beli' => 'Kemasan']);
        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'HISTORY-CANCELLED', 2))->assertOk();
        $po->penerimaanBarang()->latest('id')->firstOrFail()->update(['status' => 'cancelled']);
        $this->createReceipt($this->createOrder('HISTORY-OTHER'), 'posted', 8);

        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()
            ->assertJsonCount(2, 'received_invoices')
            ->assertJsonPath('received_invoices.0.id', $posted->id)
            ->assertJsonPath('received_invoices.0.nomor_faktur', $posted->nomor_faktur)
            ->assertJsonPath('received_invoices.0.status', 'posted')
            ->assertJsonPath('received_invoices.0.tanggal_penerimaan', '2026-10-03')
            ->assertJsonPath('received_invoices.0.details.0.nama_obat', 'Obat Picker')
            ->assertJsonPath('received_invoices.0.details.0.kode_obat', 'OBT-PICKER')
            ->assertJsonPath('received_invoices.0.details.0.qty_diterima', 4)
            ->assertJsonPath('received_invoices.0.details.0.satuan', 'PCS')
            ->assertJsonPath('received_invoices.0.details.0.no_batch', 'OLD-BATCH')
            ->assertJsonPath('received_invoices.0.details.0.expired_date', '2027-10-03')
            ->assertJsonPath('received_invoices.1.id', $draft->id)
            ->assertJsonPath('received_invoices.1.status', 'draft')
            ->assertJsonPath('received_invoices.1.tanggal_faktur', '2026-10-04')
            ->assertJsonPath('received_invoices.1.details.0.qty_diterima', 3)
            ->assertJsonPath('received_invoices.1.details.0.satuan', 'Kemasan')
            ->assertJsonPath('details.0.outstanding_qty', 3);
    }

    public function test_editing_a_receipt_only_includes_other_invoices_in_history(): void
    {
        $po = $this->createOrder('HISTORY-EDIT');
        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'HISTORY-CURRENT', 3))->assertOk();
        $current = $po->penerimaanBarang()->firstOrFail();
        $this->getJson(route('penerimaan.edit', $current->id))
            ->assertOk()->assertJsonPath('po_payload.received_invoices', []);
        $this->postJson(route('penerimaan.store'), $this->invoicePayload($po, 'HISTORY-SECOND', 7))->assertOk();
        $other = $po->penerimaanBarang()->latest('id')->firstOrFail();

        $this->getJson(route('penerimaan.edit', $current->id))
            ->assertOk()
            ->assertJsonCount(1, 'po_payload.received_invoices')
            ->assertJsonPath('po_payload.received_invoices.0.id', $other->id)
            ->assertJsonPath('po_payload.received_invoices.0.details.0.qty_diterima', 7)
            ->assertJsonPath('po_payload.details.0.outstanding_qty', 3);
    }

    public function test_invoice_history_keeps_fully_received_items_visible_and_starts_empty_for_new_orders(): void
    {
        $po = $this->createOrder('HISTORY-FULL');
        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()->assertJsonPath('received_invoices', []);
        $this->createReceipt($po, 'posted', 10);

        $this->getJson(route('penerimaan.purchaseOrderDetail', $po->id))
            ->assertOk()
            ->assertJsonPath('can_create_receipt', false)
            ->assertJsonPath('details.0.outstanding_qty', 0)
            ->assertJsonCount(1, 'received_invoices')
            ->assertJsonPath('received_invoices.0.details.0.qty_diterima', 10);
    }

    private function invoicePayload(PembelianModel $po, string $suffix, float $quantity): array
    {
        return [
            'nomor_penerimaan' => 'PB-SPLIT-'.$suffix,
            'purchase_order_id' => $po->id,
            'nomor_faktur' => 'INV-SPLIT-'.$suffix,
            'tanggal_penerimaan' => '04-10-2026',
            'tanggal_faktur' => '04-10-2026',
            'purchase_order_detail_id' => [$po->details->first()->id],
            'obat_id' => [$this->medicine->id],
            'qty_diterima' => [$quantity],
            'no_batch' => ['BATCH-'.$suffix],
            'expired_date' => ['04-10-2027'],
            'harga_beli' => [10000],
            'ppn' => [11],
        ];
    }

    private function createOrder(string $suffix, string $status = 'approved'): PembelianModel
    {
        // The receiving-status migration extends the PO enum only on MySQL.
        $extendedStatus = in_array($status, ['diterima_sebagian', 'selesai'], true) && DB::getDriverName() === 'sqlite';
        if ($extendedStatus) {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }
        try {
            $po = PembelianModel::create([
                'no_po' => 'PO-PICKER-'.$suffix, 'distributor_id' => $this->supplier->id,
                'branch_id' => $this->branch->id, 'tanggal_po' => '2026-10-03', 'status' => $status,
                'created_by' => $this->user->id, 'total_estimasi' => 99900, 'catatan' => 'Catatan pembelian picker',
            ]);
        } finally {
            if ($extendedStatus) {
                DB::statement('PRAGMA ignore_check_constraints = OFF');
            }
        }
        $po->details()->create([
            'obat_id' => $this->medicine->id, 'qty' => 10, 'harga_estimasi' => 10000,
            'diskon_1' => 10, 'ppn' => 11, 'subtotal' => 99900,
        ]);

        return $po;
    }

    private function createReceipt(PembelianModel $po, string $status, float $quantity): PenerimaanBarangModel
    {
        $receipt = PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-PICKER-'.$po->id, 'purchase_order_id' => $po->id,
            'nomor_faktur' => 'INV-PICKER-'.$po->id,
            'distributor_id' => $this->supplier->id, 'tanggal_penerimaan' => '2026-10-03',
            'status' => $status, 'created_by' => $this->user->id,
        ]);
        $receipt->details()->create([
            'purchase_order_detail_id' => $po->details->first()->id,
            'obat_id' => $this->medicine->id, 'qty_po' => 10, 'qty_diterima' => $quantity,
        ]);

        return $receipt;
    }
}
