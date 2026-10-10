<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\GolonganModel;
use App\Models\KonversiSatuanModel;
use App\Models\MainGolonganModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\SatuansModel;
use App\Models\User;
use App\Support\ControlledDrugClassification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PurchaseOrderOotSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ControlledDrugClassification::flushSettingsCache();
    }

    public function test_oot_items_are_automatically_derived_from_medicine_classification(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-OOT-PO',
            'name' => 'Cabang OOT PO',
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);

        $unit = SatuansModel::create([
            'kode' => 'TAB-OOT',
            'nama' => 'Tablet',
            'is_active' => true,
        ]);
        $distributor = DistributorModel::create([
            'kode' => 'DST-OOT',
            'nama' => 'Distributor OOT',
            'is_active' => true,
        ]);
        $classification = GolonganModel::create([
            'kode' => 'OBK-PO',
            'nama' => 'Obat Keras',
            'is_active' => true,
        ]);
        $ootClassification = MainGolonganModel::create([
            'golongan_id' => $classification->id,
            'kode' => 'OTK-OOT-PO',
            'nama' => 'OOT Keras',
        ]);

        [$firstMedicine, $firstConversion] = $this->createMedicine(
            'OBT-OOT-1',
            'Obat OOT',
            $unit,
            $distributor,
            $classification,
            $ootClassification,
        );
        [$secondMedicine, $secondConversion] = $this->createMedicine(
            'OBT-OOT-2',
            'Obat Biasa',
            $unit,
            $distributor,
            $classification,
        );

        $this->actingAs($user)
            ->postJson(route('pembelian.store'), $this->payload(
                'PO-OOT-AUTO-001',
                $distributor,
                [$firstMedicine, $secondMedicine],
                [$firstConversion, $secondConversion],
            ))
            ->assertOk();

        $purchaseOrder = PembelianModel::where('no_po', 'PO-OOT-AUTO-001')->firstOrFail();
        $details = PembelianDetailModel::where('purchase_order_id', $purchaseOrder->id)
            ->orderBy('id')
            ->get();

        $this->assertTrue($details[0]->is_oot);
        $this->assertFalse($details[1]->is_oot);

        $this->actingAs($user)
            ->getJson(route('pembelian.show', $purchaseOrder->id))
            ->assertOk()
            ->assertJsonPath('has_oot_items', true)
            ->assertJsonPath('oot_item_count', 1)
            ->assertJsonPath('has_regular_items', true)
            ->assertJsonPath('regular_item_count', 1)
            ->assertJsonPath('has_narcotic_items', false)
            ->assertJsonPath('has_psychotropic_items', false)
            ->assertJsonPath('has_precursor_items', false)
            ->assertJsonPath('details.0.is_regular', false)
            ->assertJsonPath('details.1.is_regular', true);

        $this->actingAs($user)
            ->get(route('pembelian.suratPesananReguler', $purchaseOrder->id))
            ->assertOk()
            ->assertSee('Obat Biasa')
            ->assertDontSee('Obat OOT');

        $this->actingAs($user)
            ->get(route('pembelian.suratPesananOot', $purchaseOrder->id))
            ->assertOk()
            ->assertSee('SURAT PESANAN OBAT-OBAT TERTENTU')
            ->assertSee('Obat OOT')
            ->assertDontSee('Obat Biasa');

        foreach (['Narkotika', 'Psikotropika', 'Prekursor'] as $type) {
            $this->get(route('pembelian.suratPesanan'.$type, $purchaseOrder->id))
                ->assertStatus(422);
        }

        $this->actingAs($user)
            ->getJson(route('pembelian.edit', $purchaseOrder->id))
            ->assertOk()
            ->assertJsonPath('details.0.is_oot', true)
            ->assertJsonPath('details.1.is_oot', false);

        $firstMedicine->update(['main_golongan_id' => null]);
        $secondMedicine->update(['main_golongan_id' => $ootClassification->id]);

        $this->actingAs($user)
            ->putJson(route('pembelian.update', $purchaseOrder->id), $this->payload(
                'PO-OOT-AUTO-001',
                $distributor,
                [$firstMedicine, $secondMedicine],
                [$firstConversion, $secondConversion],
            ))
            ->assertOk();

        $updatedDetails = PembelianDetailModel::where('purchase_order_id', $purchaseOrder->id)
            ->orderBy('id')
            ->get();

        $this->assertFalse($updatedDetails[0]->is_oot);
        $this->assertTrue($updatedDetails[1]->is_oot);
    }

    public function test_mixed_purchase_order_exposes_all_sp_types_and_prints_only_matching_medicines(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-SP-MIX',
            'name' => 'Cabang SP Campuran',
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);

        $unit = SatuansModel::create([
            'kode' => 'TAB-SP-MIX',
            'nama' => 'Tablet',
            'is_active' => true,
        ]);
        $distributor = DistributorModel::create([
            'kode' => 'DST-SP-MIX',
            'nama' => 'Distributor SP Campuran',
            'is_active' => true,
        ]);
        $classification = GolonganModel::create([
            'kode' => 'OBK-SP-MIX',
            'nama' => 'Obat Keras',
            'is_active' => true,
        ]);

        $types = [
            'Reguler' => 'regular',
            'Narkotika' => 'narcotic',
            'Psikotropika' => 'psychotropic',
            'Prekursor' => 'precursor',
            'Oot' => 'oot',
        ];
        $medicines = [];
        $conversions = [];

        foreach ($types as $type => $key) {
            $mainClassification = $type === 'Reguler' ? null : MainGolonganModel::create([
                'golongan_id' => $classification->id,
                'kode' => 'SP-MIX-'.$type,
                'nama' => $type,
            ]);
            [$medicine, $conversion] = $this->createMedicine(
                'OBT-SP-MIX-'.$type,
                'Obat SP '.$type,
                $unit,
                $distributor,
                $classification,
                $mainClassification,
            );
            $medicines[] = $medicine;
            $conversions[] = $conversion;
        }

        $this->actingAs($user)
            ->postJson(route('pembelian.store'), $this->payload(
                'PO-SP-MIX-001',
                $distributor,
                $medicines,
                $conversions,
            ))
            ->assertOk();

        $purchaseOrder = PembelianModel::where('no_po', 'PO-SP-MIX-001')->firstOrFail();
        $response = $this->getJson(route('pembelian.show', $purchaseOrder->id))->assertOk();

        foreach ($types as $type => $key) {
            $response->assertJsonPath('has_'.$key.'_items', true)
                ->assertJsonPath($key.'_item_count', 1);

            $letter = $this->get(route('pembelian.suratPesanan'.$type, $purchaseOrder->id))
                ->assertOk()
                ->assertSee('Obat SP '.$type);

            foreach (array_keys($types) as $otherType) {
                if ($otherType !== $type) {
                    $letter->assertDontSee('Obat SP '.$otherType);
                }
            }
        }
    }

    /**
     * @return array{0: MasterObatModel, 1: KonversiSatuanModel}
     */
    private function createMedicine(
        string $code,
        string $name,
        SatuansModel $unit,
        DistributorModel $distributor,
        GolonganModel $classification,
        ?MainGolonganModel $mainClassification = null,
    ): array {
        $medicine = MasterObatModel::create([
            'kode_obat' => $code,
            'nama_obat' => $name,
            'satuan_id' => $unit->id,
            'distributor_id' => $distributor->id,
            'golongan_id' => $classification->id,
            'main_golongan_id' => $mainClassification?->id,
            'is_active' => true,
        ]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id,
            'satuan_id' => $unit->id,
            'konversi' => 1,
            'is_default' => true,
        ]);

        return [$medicine, $conversion];
    }

    /**
     * @param  array<int, MasterObatModel>  $medicines
     * @param  array<int, KonversiSatuanModel>  $conversions
     * @return array<string, mixed>
     */
    private function payload(
        string $purchaseOrderNumber,
        DistributorModel $distributor,
        array $medicines,
        array $conversions,
    ): array {
        $itemCount = count($medicines);

        return [
            'no_po' => $purchaseOrderNumber,
            'distributor_id' => $distributor->id,
            'tanggal' => '20-08-2026',
            'catatan' => 'Klasifikasi OOT mengikuti master obat.',
            'total_estimasi' => $itemCount * 10000,
            'obat_id' => array_map(fn (MasterObatModel $medicine) => $medicine->id, $medicines),
            'qty' => array_fill(0, $itemCount, 1),
            'harga_estimasi' => array_fill(0, $itemCount, 10000),
            'diskon_1' => array_fill(0, $itemCount, 0),
            'diskon_2' => array_fill(0, $itemCount, 0),
            'diskon_3' => array_fill(0, $itemCount, 0),
            'subtotal' => array_fill(0, $itemCount, 10000),
            'satuan_id' => array_map(fn (KonversiSatuanModel $conversion) => $conversion->id, $conversions),
        ];
    }
}
