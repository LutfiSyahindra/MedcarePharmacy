<?php

namespace Tests\Feature;

use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderUnitConversionOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_unit_endpoint_returns_default_conversion_first_with_its_marker(): void
    {
        $user = User::factory()->create();
        $pieces = SatuansModel::create([
            'kode' => 'PCS-PO-UNIT',
            'nama' => 'Pieces',
            'is_active' => true,
        ]);
        $strip = SatuansModel::create([
            'kode' => 'STR-PO-UNIT',
            'nama' => 'Strip',
            'is_active' => true,
        ]);
        $box = SatuansModel::create([
            'kode' => 'BOX-PO-UNIT',
            'nama' => 'Box',
            'is_active' => true,
        ]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-PO-UNIT',
            'nama_obat' => 'Obat Konversi PO',
            'satuan_id' => $pieces->id,
            'is_active' => true,
        ]);
        $stripConversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id,
            'satuan_id' => $strip->id,
            'konversi' => 5,
            'is_default' => false,
        ]);
        $boxConversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id,
            'satuan_id' => $box->id,
            'konversi' => 10,
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('pembelian.getKonversiSatuan', [
            'obat_ids' => [$medicine->id],
        ]));

        $response->assertOk()
            ->assertJsonPath((string) $medicine->id.'.0.id', $boxConversion->id)
            ->assertJsonPath((string) $medicine->id.'.0.is_default', 1)
            ->assertJsonPath((string) $medicine->id.'.0.satuan.nama', 'Box')
            ->assertJsonPath((string) $medicine->id.'.1.id', $stripConversion->id)
            ->assertJsonPath((string) $medicine->id.'.1.is_default', 0)
            ->assertJsonPath((string) $medicine->id.'.1.satuan.nama', 'Strip');
    }
}
