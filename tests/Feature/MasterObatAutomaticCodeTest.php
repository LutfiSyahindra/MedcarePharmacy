<?php

namespace Tests\Feature;

use App\Models\CategoryModel;
use App\Models\GolonganModel;
use App\Models\MasterObatModel;
use App\Models\PabrikanModel;
use App\Models\SatuansModel;
use App\Models\SediaanModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterObatAutomaticCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_three_letter_prefix_continues_its_sequence_regardless_of_sediaan(): void
    {
        $user = User::factory()->create();
        $references = $this->createReferences('SED-061', 'Alat Kesehatan');

        MasterObatModel::create([
            'kode_obat' => 'ALK-164',
            'nama_obat' => 'Alkes Lama',
        ]);

        $this->actingAs($user)
            ->getJson(route('masterObat.nextCode', ['prefix' => 'alk']))
            ->assertOk()
            ->assertJsonPath('kode_obat', 'ALK-165');

        $response = $this->actingAs($user)
            ->postJson(route('masterObat.store'), [
                ...$this->validPayload($references),
                'kode_prefix' => 'alk',
                'kode_obat' => 'KODE-MANUAL',
                'nama_obat' => 'Alkes Baru',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.kode_obat', 'ALK-165');

        $medicineId = $response->json('data.id');

        $this->actingAs($user)
            ->putJson(route('masterObat.update', $medicineId), [
                ...$this->validPayload($references),
                'kode_obat' => 'ALK-999',
                'nama_obat' => 'Alkes Baru Diperbarui',
            ])
            ->assertOk();

        $this->assertDatabaseHas('master_obats', [
            'id' => $medicineId,
            'kode_obat' => 'ALK-165',
            'nama_obat' => 'Alkes Baru Diperbarui',
        ]);
        $this->assertDatabaseMissing('master_obats', ['kode_obat' => 'KODE-MANUAL']);
        $this->assertDatabaseMissing('master_obats', ['kode_obat' => 'ALK-999']);
    }

    public function test_new_prefix_starts_from_one(): void
    {
        $user = User::factory()->create();
        $references = $this->createReferences('SED-001', 'Tablet');

        $this->actingAs($user)
            ->postJson(route('masterObat.store'), [
                ...$this->validPayload($references),
                'kode_prefix' => 'xyz',
                'nama_obat' => 'Obat Prefix Baru',
            ])
            ->assertOk()
            ->assertJsonPath('data.kode_obat', 'XYZ-001');

        $this->assertDatabaseHas('master_obats', [
            'kode_obat' => 'XYZ-001',
            'nama_obat' => 'Obat Prefix Baru',
        ]);
    }

    public function test_creating_master_obat_also_creates_its_default_base_unit_conversion(): void
    {
        $user = User::factory()->create();
        $references = $this->createReferences('SED-002', 'Kapsul');

        $response = $this->actingAs($user)
            ->postJson(route('masterObat.store'), [
                ...$this->validPayload($references),
                'kode_prefix' => 'kps',
                'nama_obat' => 'Obat Dengan Konversi Dasar',
            ])
            ->assertOk();

        $this->assertDatabaseHas('obat_satuan_conversions', [
            'obat_id' => $response->json('data.id'),
            'satuan_id' => $references['satuan']->id,
            'konversi' => 1,
            'is_default' => true,
        ]);
        $this->assertDatabaseCount('obat_satuan_conversions', 1);
    }

    private function createReferences(string $sediaanCode, string $sediaanName): array
    {
        return [
            'category' => CategoryModel::create(['code' => 'TEST', 'name' => 'Kategori Test']),
            'golongan' => GolonganModel::create(['kode' => 'TST', 'nama' => 'Golongan Test', 'is_active' => true]),
            'sediaan' => SediaanModel::create(['kode' => $sediaanCode, 'nama' => $sediaanName, 'is_active' => true]),
            'satuan' => SatuansModel::create(['kode' => 'PCS', 'nama' => 'Pieces', 'is_active' => true]),
            'pabrikan' => PabrikanModel::create(['kode' => 'PBR', 'nama' => 'Pabrikan Test', 'is_active' => true]),
        ];
    }

    private function validPayload(array $references): array
    {
        return [
            'nama_obat' => 'Obat Test',
            'sediaan_id' => $references['sediaan']->id,
            'category_id' => $references['category']->id,
            'golongan_id' => $references['golongan']->id,
            'satuan_id' => $references['satuan']->id,
            'pabrikan_id' => $references['pabrikan']->id,
            'stok_minimum' => 0,
            'harga_beli' => 1000,
            'is_generik' => true,
            'is_active' => true,
        ];
    }
}
