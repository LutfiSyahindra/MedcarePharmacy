<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\GolonganModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SuratPesananPrintSignatureTest extends TestCase
{
    use RefreshDatabase;

    private BranchModel $branch;

    private PembelianModel $purchaseOrder;

    private const COPY_COUNTS = [
        'Reguler' => 2,
        'Narkotika' => 5,
        'Psikotropika' => 5,
        'Prekursor' => 4,
        'Oot' => 4,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'SP-SIGN',
            'name' => 'Cabang Cetak SP',
            'is_active' => true,
        ]);
        $user->branches()->attach($this->branch->id);
        ApotekProfile::create([
            'branch_id' => $this->branch->id,
            'name' => 'Apotek Cetak SP',
            'address' => 'Jl. Sehat No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'pharmacist_name' => 'apt. Siti Sehat',
            'pharmacist_license_number' => 'SIPA-SIGN',
            'pharmacist_signature_path' => 'apotek-signatures/print-test.png',
        ]);
        $unit = SatuansModel::create(['kode' => 'SP-TAB', 'nama' => 'Tablet', 'is_active' => true]);
        $distributor = DistributorModel::create(['kode' => 'SP-DIST', 'nama' => 'Distributor SP', 'is_active' => true]);
        $this->purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-SIGN-001',
            'branch_id' => $this->branch->id,
            'distributor_id' => $distributor->id,
            'tanggal_po' => '2026-10-12',
            'created_by' => $user->id,
        ]);

        foreach (['OBK' => 'Obat Keras', 'NAR' => 'Narkotika', 'PSI' => 'Psikotropika', 'PRE' => 'Prekursor', 'OOT' => 'Obat-Obat Tertentu'] as $code => $name) {
            $classification = GolonganModel::create(['kode' => $code, 'nama' => $name, 'is_active' => true]);
            $medicine = MasterObatModel::create([
                'kode_obat' => 'SP-'.$code,
                'nama_obat' => 'Obat '.$code,
                'golongan_id' => $classification->id,
                'satuan_id' => $unit->id,
                'distributor_id' => $distributor->id,
                'is_active' => true,
            ]);
            PembelianDetailModel::create([
                'purchase_order_id' => $this->purchaseOrder->id,
                'obat_id' => $medicine->id,
                'qty' => 1,
                'harga_estimasi' => 1000,
                'subtotal' => 1000,
            ]);
        }

        $this->actingAs($user);
    }

    public function test_all_order_types_can_print_with_or_without_the_saved_signature(): void
    {
        foreach (self::COPY_COUNTS as $type => $copies) {
            foreach ([[], ['with_signature' => 1], ['with_signature' => 0]] as $options) {
                $response = $this->get(route('pembelian.suratPesanan'.$type, [
                    'id' => $this->purchaseOrder->id,
                    'print' => 1,
                    ...$options,
                ]));

                $this->assertSignatureOutput($response, $options['with_signature'] ?? 1, $copies);
                $response->assertSee('PO-SIGN-001')->assertSee('window.setTimeout', false);
            }
        }
    }

    public function test_all_order_templates_can_print_with_or_without_the_saved_signature(): void
    {
        foreach (self::COPY_COUNTS as $type => $copies) {
            foreach ([[], ['with_signature' => 1], ['with_signature' => 0]] as $options) {
                $response = $this->get(route('dokumen.template', [
                    'type' => strtolower($type),
                    'branch_id' => $this->branch->id,
                    'print' => 1,
                    ...$options,
                ]));

                $this->assertSignatureOutput($response, $options['with_signature'] ?? 1, $copies);
                $response->assertSee('Template kosong')->assertSee('window.setTimeout', false);
            }
        }
    }

    public function test_printing_with_signature_without_an_uploaded_image_keeps_the_manual_signature_space(): void
    {
        $this->branch->apotekProfile->update(['pharmacist_signature_path' => null]);

        foreach (self::COPY_COUNTS as $type => $copies) {
            $response = $this->get(route('pembelian.suratPesanan'.$type, [
                'id' => $this->purchaseOrder->id,
                'with_signature' => 1,
            ]));

            $this->assertSignatureOutput($response, 0, $copies);
            $response->assertDontSee('window.setTimeout', false);
        }
    }

    private function assertSignatureOutput(TestResponse $response, int $withSignature, int $copies): void
    {
        $response->assertOk()
            ->assertSee('Dengan TTD')
            ->assertSee('Tanpa TTD')
            ->assertSee('apt. Siti Sehat')
            ->assertSee('SIPA-SIGN');

        $html = $response->getContent();
        $this->assertSame($withSignature ? $copies : 0, substr_count($html, 'class="pharmacist-signature-image"'));
        $this->assertSame($withSignature ? $copies : 0, substr_count($html, asset('storage/apotek-signatures/print-test.png')));
        $this->assertSame($withSignature ? 0 : $copies, substr_count($html, 'class="signature-space"'));
    }
}
