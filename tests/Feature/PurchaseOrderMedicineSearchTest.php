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
use Tests\TestCase;

class PurchaseOrderMedicineSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private BranchModel $secondBranch;

    private BranchModel $otherBranch;

    private DistributorModel $distributor;

    private MasterObatModel $medicine;

    private MasterObatModel $otherMedicine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create(['code' => 'PO-SEARCH-1', 'name' => 'Cabang 1', 'is_active' => true]);
        $this->secondBranch = BranchModel::create(['code' => 'PO-SEARCH-2', 'name' => 'Cabang 2', 'is_active' => true]);
        $this->otherBranch = BranchModel::create(['code' => 'PO-SEARCH-3', 'name' => 'Cabang 3', 'is_active' => true]);
        $this->user->branches()->attach([$this->branch->id, $this->secondBranch->id]);
        $this->distributor = DistributorModel::create([
            'kode' => 'DST-PO-SEARCH', 'nama' => 'Distributor Obat B', 'is_active' => true,
        ]);
        $unit = SatuansModel::create(['kode' => 'TAB-PO-SEARCH', 'nama' => 'Tablet', 'is_active' => true]);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'MED-B-500', 'nama_obat' => 'Obat B 500 mg', 'satuan_id' => $unit->id, 'is_active' => true,
        ]);
        $this->otherMedicine = MasterObatModel::create([
            'kode_obat' => 'MED-A-250', 'nama_obat' => 'Obat A 250 mg', 'satuan_id' => $unit->id, 'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    public function test_name_search_finds_each_accessible_po_once_and_returns_matching_items_with_units(): void
    {
        $first = $this->createOrder($this->branch, '2026-01-01', 'draft');
        $this->addItem($first, $this->medicine, 4);
        $box = SatuansModel::create(['kode' => 'BOX-PO-SEARCH', 'nama' => 'Box', 'is_active' => true]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $this->medicine->id, 'satuan_id' => $box->id, 'konversi' => 10, 'is_default' => false,
        ]);
        $this->addItem($first, $this->medicine, 2)->update(['satuan_konversi' => $conversion->id]);
        $this->addItem($first, $this->otherMedicine, 7);
        $second = $this->createOrder($this->secondBranch, '2026-10-03', 'approved');
        $this->addItem($second, $this->medicine, 3);
        $this->addItem($this->createOrder($this->branch), $this->otherMedicine, 9);
        $this->addItem($this->createOrder($this->otherBranch), $this->medicine, 99);
        $this->createOrder($this->branch);

        $response = $this->search('  oBaT b  ')->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.draft', 1)
            ->assertJsonPath('summary.approved', 1)
            ->assertJsonPath('summary.total_item', 4)
            ->assertJsonCount(2, 'data');

        $rows = collect($response->json('data'))->keyBy('id');
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $rows->keys()->all());
        $this->assertSame(3, $rows[$first->id]['item_count']);
        $this->assertCount(2, $rows[$first->id]['matched_medicines']);
        $this->assertSame(['Obat B 500 mg', 'Obat B 500 mg'], array_column($rows[$first->id]['matched_medicines'], 'nama_obat'));
        $this->assertSame(['Tablet', 'Box'], array_column($rows[$first->id]['matched_medicines'], 'satuan'));
        $this->assertSame([4, 2], array_column($rows[$first->id]['matched_medicines'], 'qty'));
    }

    public function test_code_search_includes_inactive_medicines_in_historical_purchase_orders(): void
    {
        $order = $this->createOrder($this->branch, '2025-01-01');
        $this->addItem($order, $this->medicine, 5);
        $this->addItem($this->createOrder($this->branch), $this->otherMedicine, 2);
        $this->medicine->update(['is_active' => false]);

        $this->search('med-b')->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $order->id)
            ->assertJsonPath('data.0.matched_medicines.0.kode_obat', 'MED-B-500');
    }

    public function test_dropdown_selection_matches_the_exact_medicine_even_when_names_and_codes_are_similar(): void
    {
        $this->otherMedicine->update([
            'nama_obat' => $this->medicine->nama_obat,
            'kode_obat' => $this->medicine->kode_obat.'-ALT',
        ]);
        $order = $this->createOrder($this->branch, '2025-01-01');
        $this->addItem($order, $this->medicine, 4);
        $this->addItem($order, $this->otherMedicine, 7);
        $this->addItem($this->createOrder($this->branch), $this->otherMedicine, 2);
        $this->addItem($this->createOrder($this->otherBranch), $this->medicine, 99);
        $this->medicine->update(['is_active' => false]);

        $this->getJson(route('pembelian.table', ['medicine_id' => $this->medicine->id]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $order->id)
            ->assertJsonPath('data.0.item_count', 2)
            ->assertJsonCount(1, 'data.0.matched_medicines')
            ->assertJsonPath('data.0.matched_medicines.0.obat_id', $this->medicine->id)
            ->assertJsonPath('data.0.matched_medicines.0.qty', 4);

        $this->getJson(route('pembelian.table', ['medicine_id' => '']))
            ->assertOk()->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(0, 'data.0.matched_medicines');
    }

    public function test_name_search_treats_sql_wildcards_and_escape_characters_as_literal_text(): void
    {
        $this->medicine->update(['nama_obat' => 'Vitamin_C 10% !']);
        $this->otherMedicine->update(['nama_obat' => 'VitaminXC 10X']);
        $order = $this->createOrder($this->branch);
        $this->addItem($order, $this->medicine, 1);
        $this->addItem($this->createOrder($this->branch), $this->otherMedicine, 2);

        foreach (['Vitamin_C', '10%', '!'] as $term) {
            $this->search($term)->assertOk()
                ->assertJsonPath('recordsFiltered', 1)
                ->assertJsonPath('data.0.id', $order->id);
        }

        $this->search("' OR 1=1 --")->assertOk()->assertJsonPath('recordsFiltered', 0);
    }

    public function test_medicine_results_can_be_narrowed_by_dates_status_and_paginated(): void
    {
        $old = $this->createOrder($this->branch, '2026-01-01', 'draft');
        $this->addItem($old, $this->medicine, 1);
        $current = $this->createOrder($this->branch, '2026-10-02', 'draft');
        $this->addItem($current, $this->medicine, 2);
        $approved = $this->createOrder($this->branch, '2026-10-03', 'approved');
        $this->addItem($approved, $this->medicine, 3);

        $this->search('Obat B', [
            'date_start' => '2026-10-01', 'date_end' => '2026-10-31',
            'columns' => [[
                'data' => 'status', 'name' => 'status', 'searchable' => 'true',
                'search' => ['value' => 'draft', 'regex' => 'false'],
            ]],
        ])->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $current->id)
            ->assertJsonPath('summary.total', 2);

        $this->search('Obat B', ['start' => 1, 'length' => 1])->assertOk()
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(1, 'data');
    }

    public function test_blank_search_restores_orders_and_unknown_medicine_returns_empty_results(): void
    {
        $this->addItem($this->createOrder($this->branch), $this->medicine, 1);
        $this->addItem($this->createOrder($this->branch), $this->otherMedicine, 2);
        $this->search('Tidak ditemukan')->assertOk()
            ->assertJsonPath('recordsFiltered', 0)
            ->assertJsonPath('summary.total', 0)
            ->assertJsonCount(0, 'data');

        $this->search('   ')->assertOk()
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(0, 'data.0.matched_medicines');
    }

    public function test_user_without_branch_access_cannot_find_matching_orders(): void
    {
        $this->addItem($this->createOrder($this->branch), $this->medicine, 1);
        $this->actingAs(User::factory()->create());
        $this->search('Obat B')->assertOk()->assertJsonPath('recordsFiltered', 0);
        $this->getJson(route('pembelian.table', ['medicine_id' => $this->medicine->id]))
            ->assertOk()->assertJsonPath('recordsFiltered', 0);
    }

    public function test_invalid_search_terms_are_rejected(): void
    {
        foreach ([['Obat B'], str_repeat('a', 151)] as $term) {
            $this->getJson(route('pembelian.table', ['medicine_search' => $term]))
                ->assertUnprocessable()->assertJsonValidationErrors('medicine_search');
        }
    }

    public function test_invalid_dropdown_selection_is_rejected(): void
    {
        foreach ([[$this->medicine->id], 'invalid', 999999] as $medicineId) {
            $this->getJson(route('pembelian.table', ['medicine_id' => $medicineId]))
                ->assertUnprocessable()->assertJsonValidationErrors('medicine_id');
        }
    }

    private function search(string $term, array $parameters = [])
    {
        return $this->getJson(route('pembelian.table', ['medicine_search' => $term, ...$parameters]));
    }

    private function createOrder(BranchModel $branch, string $date = '2026-10-03', string $status = 'draft'): PembelianModel
    {
        return PembelianModel::create([
            'no_po' => 'PO-SEARCH-'.(PembelianModel::count() + 1),
            'distributor_id' => $this->distributor->id,
            'branch_id' => $branch->id,
            'tanggal_po' => $date,
            'status' => $status,
            'created_by' => $this->user->id,
        ]);
    }

    private function addItem(PembelianModel $order, MasterObatModel $medicine, int $quantity): PembelianDetailModel
    {
        return PembelianDetailModel::create([
            'purchase_order_id' => $order->id,
            'obat_id' => $medicine->id,
            'qty' => $quantity,
            'harga_estimasi' => 1000,
            'subtotal' => $quantity * 1000,
        ]);
    }
}
