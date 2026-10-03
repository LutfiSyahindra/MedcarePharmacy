<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenerimaanMedicineSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PembelianModel $purchaseOrder;

    private MasterObatModel $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-MED-SEARCH', 'name' => 'Cabang Pencarian Obat', 'is_active' => true,
        ]);
        $this->user->branches()->attach($branch->id);
        $supplier = DistributorModel::create([
            'kode' => 'DST-MED-SEARCH', 'nama' => 'Supplier Pencarian Obat', 'is_active' => true,
        ]);
        $this->purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-MED-SEARCH', 'distributor_id' => $supplier->id,
            'branch_id' => $branch->id, 'tanggal_po' => '2026-08-01', 'status' => 'approved',
            'created_by' => $this->user->id,
        ]);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'PAR-500', 'nama_obat' => 'Paracetamol 500 mg', 'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    public function test_selected_medicine_finds_all_receipts_by_exact_id_without_duplicate_batch_rows(): void
    {
        $old = $this->createReceipt($this->medicine, 'posted', '2026-08-01');
        $old->details()->create([
            'obat_id' => $this->medicine->id, 'qty_diterima' => 2.5,
            'satuan_beli' => 'BOX', 'no_batch' => 'BATCH-SECOND', 'expired_date' => '2028-02-01',
        ]);
        $otherMedicine = MasterObatModel::create([
            'kode_obat' => 'PAR-OTHER', 'nama_obat' => $this->medicine->nama_obat,
        ]);
        $old->details()->create(['obat_id' => $otherMedicine->id, 'qty_diterima' => 9]);
        $draft = $this->createReceipt($this->medicine, 'draft', '2026-09-01');
        $cancelled = $this->createReceipt($this->medicine, 'cancelled', '2026-10-01');
        $this->createReceipt($otherMedicine, 'posted', '2026-10-02');

        $response = $this->getJson(route('penerimaan.table', ['obat_id' => $this->medicine->id]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('summary.total', 3)
            ->assertJsonPath('summary.draft', 1)
            ->assertJsonPath('summary.posted', 1)
            ->assertJsonPath('summary.cancelled', 1);
        $rows = collect($response->json('data'))->keyBy('id');
        $this->assertEqualsCanonicalizing([$old->id, $draft->id, $cancelled->id], $rows->keys()->all());
        $this->assertCount(2, $rows[$old->id]['obat_dipilih_details']);
        $this->assertSame([$this->medicine->id, $this->medicine->id], array_column($rows[$old->id]['obat_dipilih_details'], 'obat_id'));
        $this->assertSame('BOX', $rows[$old->id]['obat_dipilih_details'][1]['satuan']);
        $this->assertEquals(2.5, $rows[$old->id]['obat_dipilih_details'][1]['qty_diterima']);
        $this->assertSame('BATCH-SECOND', $rows[$old->id]['obat_dipilih_details'][1]['no_batch']);
        $this->assertSame('2028-02-01', $rows[$old->id]['obat_dipilih_details'][1]['expired_date']);

        $this->getJson(route('penerimaan.table', ['obat_id' => 999999]))
            ->assertOk()->assertJsonPath('recordsTotal', 0);
        $this->getJson(route('penerimaan.table', ['obat_id' => '']))
            ->assertOk()->assertJsonPath('recordsTotal', 4)
            ->assertJsonPath('data.0.obat_dipilih_details', []);
    }

    public function test_medicine_filter_combines_with_purchase_order_date_status_and_invoice_search(): void
    {
        $this->createReceipt($this->medicine, 'posted', '2026-08-01');
        $this->createReceipt($this->medicine, 'draft', '2026-09-01');
        $posted = $this->createReceipt($this->medicine, 'posted', '2026-09-02');
        $otherOrder = $this->purchaseOrder->replicate();
        $otherOrder->no_po = 'PO-MED-SEARCH-OTHER';
        $otherOrder->save();
        $this->createReceipt($this->medicine, 'posted', '2026-09-02', $otherOrder);

        $this->getJson(route('penerimaan.table', [
            'obat_id' => $this->medicine->id,
            'purchase_order_id' => $this->purchaseOrder->id,
            'date_start' => '2026-09-01', 'date_end' => '2026-09-30',
            'columns' => [
                ['data' => 'status', 'searchable' => 'true', 'search' => ['value' => 'posted']],
                ['data' => 'nomor_faktur', 'searchable' => 'true', 'search' => ['value' => '']],
            ],
            'search' => ['value' => $posted->nomor_faktur],
        ]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $posted->id);
    }

    public function test_medicine_picker_searches_name_and_code_across_all_dates_and_includes_inactive_history(): void
    {
        $receipt = $this->createReceipt($this->medicine, 'cancelled', '2026-08-01');
        $receipt->details()->create(['obat_id' => $this->medicine->id, 'qty_diterima' => 2]);
        $this->medicine->update(['is_active' => false]);
        MasterObatModel::create(['kode_obat' => 'UNRECEIVED', 'nama_obat' => 'Paracetamol belum diterima']);

        foreach (['Paracetamol', 'PAR-500', ''] as $search) {
            $this->getJson(route('penerimaan.receiptMedicines', ['q' => $search]))
                ->assertOk()
                ->assertJsonCount(1, 'results')
                ->assertJsonPath('results.0.id', $this->medicine->id)
                ->assertJsonPath('results.0.text', 'Paracetamol 500 mg (PAR-500)')
                ->assertJsonPath('results.0.nama_obat', 'Paracetamol 500 mg')
                ->assertJsonPath('results.0.kode_obat', 'PAR-500')
                ->assertJsonPath('pagination.more', false);
        }
        $this->getJson(route('penerimaan.receiptMedicines', ['q' => 'Tidak ada']))
            ->assertOk()->assertJsonCount(0, 'results');

        $otherOrder = $this->purchaseOrder->replicate();
        $otherOrder->no_po = 'PO-WITHOUT-RECEIPTS';
        $otherOrder->save();
        $this->getJson(route('penerimaan.receiptMedicines', ['purchase_order_id' => $otherOrder->id]))
            ->assertOk()->assertJsonCount(0, 'results');
    }

    public function test_medicine_picker_paginates_without_duplicate_or_missing_medicines(): void
    {
        $receipt = $this->createReceipt($this->medicine);
        for ($index = 1; $index <= 21; $index++) {
            $medicine = MasterObatModel::create([
                'kode_obat' => 'MED-PAGE-'.$index, 'nama_obat' => 'Obat '.str_pad($index, 2, '0', STR_PAD_LEFT),
            ]);
            $receipt->details()->create(['obat_id' => $medicine->id, 'qty_diterima' => 1]);
        }

        $first = $this->getJson(route('penerimaan.receiptMedicines', ['page' => 1]))
            ->assertOk()->assertJsonCount(20, 'results')->assertJsonPath('pagination.more', true);
        $second = $this->getJson(route('penerimaan.receiptMedicines', ['page' => 2]))
            ->assertOk()->assertJsonCount(2, 'results')->assertJsonPath('pagination.more', false);
        $ids = array_merge(array_column($first->json('results'), 'id'), array_column($second->json('results'), 'id'));
        $this->assertCount(22, array_unique($ids));
    }

    public function test_matched_medicine_text_is_escaped_for_display(): void
    {
        $this->medicine->update(['nama_obat' => 'Obat & <script>alert(1)</script>']);
        $receipt = $this->createReceipt($this->medicine);
        $receipt->details()->update(['no_batch' => '<img src=x onerror=alert(1)>', 'satuan_beli' => 'BOX & PCS']);

        $this->getJson(route('penerimaan.table', ['obat_id' => $this->medicine->id]))
            ->assertOk()
            ->assertJsonPath('data.0.obat_dipilih_details.0.nama_obat', 'Obat &amp; &lt;script&gt;alert(1)&lt;/script&gt;')
            ->assertJsonPath('data.0.obat_dipilih_details.0.satuan', 'BOX &amp; PCS')
            ->assertJsonPath('data.0.obat_dipilih_details.0.no_batch', '&lt;img src=x onerror=alert(1)&gt;');
    }

    public function test_medicine_picker_and_receipt_filter_respect_branch_access(): void
    {
        $visible = $this->createReceipt($this->medicine);
        $branch = BranchModel::create(['code' => 'CB-MED-OTHER', 'name' => 'Cabang Lain', 'is_active' => true]);
        $otherOrder = $this->purchaseOrder->replicate();
        $otherOrder->no_po = 'PO-MED-OTHER-BRANCH';
        $otherOrder->branch_id = $branch->id;
        $otherOrder->save();
        $hiddenMedicine = MasterObatModel::create(['kode_obat' => 'HIDDEN', 'nama_obat' => 'Obat Cabang Lain']);
        $this->createReceipt($hiddenMedicine, 'posted', '2026-10-01', $otherOrder);
        $this->createReceipt($this->medicine, 'posted', '2026-10-01', $otherOrder);

        $this->getJson(route('penerimaan.receiptMedicines'))
            ->assertOk()->assertJsonCount(1, 'results')->assertJsonPath('results.0.id', $this->medicine->id);
        $this->getJson(route('penerimaan.receiptMedicines', ['q' => 'HIDDEN']))
            ->assertOk()->assertJsonCount(0, 'results');
        $this->getJson(route('penerimaan.table', ['obat_id' => $this->medicine->id]))
            ->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.id', $visible->id);
        $this->getJson(route('penerimaan.table', ['obat_id' => $hiddenMedicine->id]))
            ->assertOk()->assertJsonPath('recordsTotal', 0);

        $this->actingAs(User::factory()->create());
        $this->getJson(route('penerimaan.receiptMedicines'))->assertOk()->assertJsonCount(0, 'results');
        $this->getJson(route('penerimaan.table', ['obat_id' => $this->medicine->id]))
            ->assertOk()->assertJsonPath('recordsTotal', 0);
    }

    public function test_medicine_search_rejects_invalid_input_and_renders_the_filter(): void
    {
        foreach (['invalid', 0, -1, [$this->medicine->id]] as $id) {
            $this->getJson(route('penerimaan.table', ['obat_id' => $id]))
                ->assertUnprocessable()->assertJsonValidationErrors('obat_id');
        }
        $this->getJson(route('penerimaan.receiptMedicines', ['q' => ['invalid']]))
            ->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson(route('penerimaan.receiptMedicines', ['q' => str_repeat('a', 101)]))
            ->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson(route('penerimaan.receiptMedicines', ['page' => 0]))
            ->assertUnprocessable()->assertJsonValidationErrors('page');
        $this->getJson(route('penerimaan.receiptMedicines', ['purchase_order_id' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors('purchase_order_id');
        $this->get(route('penerimaan.penerimaan'))
            ->assertOk()->assertSee('Cari Obat dalam Penerimaan')->assertSee('id="receiveMedicineFilter"', false);
    }

    private function createReceipt(
        MasterObatModel $medicine,
        string $status = 'draft',
        string $date = '2026-10-01',
        ?PembelianModel $purchaseOrder = null
    ): PenerimaanBarangModel {
        $purchaseOrder ??= $this->purchaseOrder;
        $number = 'PB-MED-SEARCH-'.(PenerimaanBarangModel::max('id') + 1);
        $receipt = PenerimaanBarangModel::create([
            'nomor_penerimaan' => $number, 'nomor_faktur' => 'INV-'.$number,
            'purchase_order_id' => $purchaseOrder->id, 'distributor_id' => $purchaseOrder->distributor_id,
            'tanggal_penerimaan' => $date, 'status' => $status, 'created_by' => $this->user->id,
        ]);
        $receipt->details()->create([
            'obat_id' => $medicine->id, 'qty_diterima' => 3, 'satuan_beli' => 'BOX',
            'no_batch' => 'BATCH-FIRST', 'expired_date' => '2028-01-01',
        ]);

        return $receipt;
    }
}
