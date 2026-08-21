<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\GolonganModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\SatuansModel;
use App\Models\SediaanModel;
use App\Services\Menu\PembelianPenerimaan\SuratPesananRegulerService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class SuratPesananRegulerTest extends TestCase
{
    public function test_regular_order_only_contains_non_controlled_and_non_oot_medicines(): void
    {
        $service = app(SuratPesananRegulerService::class);
        $purchaseOrder = $this->purchaseOrderForView();

        $regularDetails = $service->regularDetails($purchaseOrder);

        $this->assertCount(1, $regularDetails);
        $this->assertSame('Paracetamol 500 mg', $regularDetails->first()->obat->nama_obat);
        $this->assertTrue($regularDetails->first()->is_regular);
        $this->assertSame('PO-REG-001/REG', $purchaseOrder->regular_document_number);
    }

    public function test_regular_order_combines_matching_items_in_two_copies(): void
    {
        $service = app(SuratPesananRegulerService::class);
        $purchaseOrder = $this->purchaseOrderForView();
        $regularDetails = $service->regularDetails($purchaseOrder);

        $html = view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananReguler', [
            'purchaseOrder' => $purchaseOrder,
            'regularDetails' => $regularDetails,
            'copyCount' => SuratPesananRegulerService::COPY_COUNT,
            'autoPrint' => false,
        ])->render();

        $this->assertSame(2, substr_count($html, 'class="regular-order-sheet"'));
        $this->assertSame(2, substr_count($html, 'PO-REG-001/REG'));
        $this->assertStringContainsString('SURAT PESANAN OBAT REGULER', $html);
        $this->assertStringContainsString('Paracetamol 500 mg', $html);
        $this->assertSame(1, substr_count($html, 'class="copy-role-badge">Lembar Internal Apotek'));
        $this->assertSame(1, substr_count($html, 'class="copy-role-badge">Lembar Distributor'));
        $this->assertStringContainsString('>Obat</th>', $html);
        $this->assertStringContainsString('>Satuan</th>', $html);
        $this->assertStringContainsString('>Qty</th>', $html);
        $this->assertStringContainsString('>Harga dasar</th>', $html);
        $this->assertStringContainsString('>Harga total</th>', $html);
        $this->assertStringContainsString('>Diskon</th>', $html);
        $this->assertStringContainsString('Rp 1.500', $html);
        $this->assertStringContainsString('Rp 25.650', $html);
        $this->assertStringContainsString('D1 10%', $html);
        $this->assertStringContainsString('D2 5%', $html);
        $this->assertStringContainsString('D3 0%', $html);
        $this->assertStringNotContainsString('Bentuk sediaan', $html);
        $this->assertStringNotContainsString('Kekuatan/komposisi', $html);
        $this->assertStringNotContainsString('Kemasan', $html);
        $this->assertStringNotContainsString('angka dan huruf', $html);
        $this->assertStringNotContainsString('Morphine 10 mg', $html);
        $this->assertStringNotContainsString('Dextromethorphan 15 mg', $html);
    }

    private function purchaseOrderForView(): PembelianModel
    {
        $profile = (new ApotekProfile)->forceFill([
            'name' => 'Apotek Medcare Pusat',
            'address' => 'Jl. Sehat No. 1',
            'city' => 'Jakarta',
            'pharmacist_name' => 'apt. Siti Sehat, S.Farm.',
            'pharmacist_license_number' => 'SIPA-005',
        ]);
        $branch = (new BranchModel)->forceFill(['name' => 'Cabang Pusat']);
        $branch->setRelation('apotekProfile', $profile);

        $tablet = new SatuansModel(['nama' => 'Tablet']);
        $conversion = new KonversiSatuanModel;
        $conversion->setRelation('satuan', $tablet);

        $regular = $this->medicine('OBK', 'Obat Keras', 'Paracetamol 500 mg', $tablet);
        $narcotic = $this->medicine('NAR', 'Narkotika', 'Morphine 10 mg', $tablet);
        $oot = $this->medicine('OBK', 'Obat Keras', 'Dextromethorphan 15 mg', $tablet);

        $purchaseOrder = (new PembelianModel)->forceFill([
            'no_po' => 'PO-REG-001',
            'tanggal_po' => '2026-08-20',
        ]);
        $purchaseOrder->setRelation('branch', $branch);
        $purchaseOrder->setRelation('distributor', new DistributorModel([
            'nama' => 'PT Distributor Reguler',
            'alamat' => 'Jl. Distribusi No. 12',
            'telepon' => '0215550505',
        ]));
        $purchaseOrder->setRelation('details', new Collection([
            $this->detail($regular, $conversion, 20),
            $this->detail($narcotic, $conversion, 5),
            $this->detail($oot, $conversion, 8, true),
        ]));

        return $purchaseOrder;
    }

    private function medicine(
        string $code,
        string $classification,
        string $name,
        SatuansModel $unit,
    ): MasterObatModel {
        $medicine = (new MasterObatModel)->forceFill([
            'nama_obat' => $name,
            'komposisi' => $name,
            'kemasan' => 'Box 10 strip',
        ]);
        $medicine->setRelation('golongan', new GolonganModel(['kode' => $code, 'nama' => $classification]));
        $medicine->setRelation('mainGolongan', null);
        $medicine->setRelation('subGolongan', null);
        $medicine->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $medicine->setRelation('satuan', $unit);

        return $medicine;
    }

    private function detail(
        MasterObatModel $medicine,
        KonversiSatuanModel $conversion,
        int $quantity,
        bool $isOot = false,
    ): PembelianDetailModel {
        $detail = (new PembelianDetailModel)->forceFill([
            'qty' => $quantity,
            'harga_estimasi' => 1500,
            'diskon_1' => 10,
            'diskon_2' => 5,
            'diskon_3' => 0,
            'is_oot' => $isOot,
        ]);
        $detail->setRelation('obat', $medicine);
        $detail->setRelation('satuanKonversi', $conversion);

        return $detail;
    }
}
