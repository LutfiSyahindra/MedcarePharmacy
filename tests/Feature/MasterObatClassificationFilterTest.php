<?php

namespace Tests\Feature;

use App\Models\CategoryModel;
use App\Models\GolonganModel;
use App\Models\MainGolonganModel;
use App\Models\MasterObatModel;
use App\Models\SubGolonganModel;
use App\Models\User;
use App\Services\Settings\Master\MasterObatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterObatClassificationFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_options_include_item_counts_hierarchy_and_unclassified_items(): void
    {
        $user = User::factory()->create();
        $category = CategoryModel::create([
            'code' => 'KAT-ANL',
            'name' => 'Analgesik',
        ]);
        $golongan = GolonganModel::create([
            'kode' => 'GOL-KRS',
            'nama' => 'Obat Keras',
            'is_active' => true,
        ]);
        $mainGolongan = MainGolonganModel::create([
            'golongan_id' => $golongan->id,
            'kode' => 'MAIN-OOT',
            'nama' => 'Obat Tertentu',
        ]);
        $subGolongan = SubGolonganModel::create([
            'main_golongan_id' => $mainGolongan->id,
            'kode' => 'SUB-TMD',
            'nama' => 'Tramadol',
        ]);

        MasterObatModel::create([
            'kode_obat' => 'OBT-FLT-001',
            'nama_obat' => 'Obat Filter Satu',
            'category_id' => $category->id,
            'golongan_id' => $golongan->id,
            'main_golongan_id' => $mainGolongan->id,
            'sub_golongan_id' => $subGolongan->id,
        ]);
        MasterObatModel::create([
            'kode_obat' => 'OBT-FLT-002',
            'nama_obat' => 'Obat Filter Dua',
            'category_id' => $category->id,
            'golongan_id' => $golongan->id,
            'main_golongan_id' => $mainGolongan->id,
        ]);
        MasterObatModel::create([
            'kode_obat' => 'OBT-FLT-003',
            'nama_obat' => 'Obat Tanpa Klasifikasi',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('masterObat.filterOptions'))
            ->assertOk()
            ->assertJsonPath('total', 3);

        $filters = $response->json('filters');
        $categoryOption = collect($filters['categories'])->firstWhere('id', (string) $category->id);
        $golonganOption = collect($filters['golongan'])->firstWhere('id', (string) $golongan->id);
        $mainOption = collect($filters['main_golongan'])->firstWhere('id', (string) $mainGolongan->id);
        $subOption = collect($filters['sub_golongan'])->firstWhere('id', (string) $subGolongan->id);

        $this->assertSame(2, $categoryOption['count']);
        $this->assertSame(2, $golonganOption['count']);
        $this->assertSame(2, $mainOption['count']);
        $this->assertSame('Obat Keras', $mainOption['golongan_name']);
        $this->assertSame(1, $subOption['count']);
        $this->assertSame('Obat Tertentu', $subOption['main_golongan_name']);
        $this->assertSame(1, collect($filters['categories'])->firstWhere('id', '__none__')['count']);
        $this->assertSame(1, collect($filters['golongan'])->firstWhere('id', '__none__')['count']);
        $this->assertSame(1, collect($filters['main_golongan'])->firstWhere('id', '__none__')['count']);
        $this->assertSame(2, collect($filters['sub_golongan'])->firstWhere('id', '__none__')['count']);

        $tableRows = collect(app(MasterObatService::class)->getMasterObatTable());
        $classifiedRow = $tableRows->firstWhere('kode_obat', 'OBT-FLT-001');
        $unclassifiedRow = $tableRows->firstWhere('kode_obat', 'OBT-FLT-003');

        $this->assertSame('|category:'.$category->id.'|', $classifiedRow['category_filter']);
        $this->assertSame('|sub_golongan:'.$subGolongan->id.'|', $classifiedRow['sub_golongan_filter']);
        $this->assertSame('|category:none|', $unclassifiedRow['category_filter']);
        $this->assertSame('|sub_golongan:none|', $unclassifiedRow['sub_golongan_filter']);

        $this->getJson(route('masterObat.table', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'classification_filters' => [
                'category' => '|category:'.$category->id.'|',
                'golongan' => '|golongan:'.$golongan->id.'|',
                'main_golongan' => '',
                'sub_golongan' => '',
            ],
        ]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data');
    }
}
