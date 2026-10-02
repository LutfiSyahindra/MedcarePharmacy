<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\GolonganModel;
use App\Models\KonversiSatuanModel;
use App\Models\MainGolonganModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\SatuansModel;
use App\Models\SediaanModel;
use App\Services\Menu\PembelianPenerimaan\SuratPesananOotService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class SuratPesananOotTest extends TestCase
{
    public function test_oot_classification_is_derived_from_the_medicine_master(): void
    {
        $service = app(SuratPesananOotService::class);
        $tablet = new SatuansModel(['nama' => 'Tablet']);
        $ootMedicine = $this->medicine('Dextromethorphan 15 mg', 'Box 10 strip', $tablet, true);
        $regularMedicine = $this->medicine('Paracetamol 500 mg', 'Box 10 strip', $tablet);

        $this->assertTrue($service->isOotDrug($ootMedicine));
        $this->assertFalse($service->isOotDrug($regularMedicine));
        $this->assertSame('Main Golongan: OOT Keras', $service->matchedClassification($ootMedicine));
    }

    public function test_oot_items_follow_medicine_classification(): void
    {
        $service = app(SuratPesananOotService::class);
        $purchaseOrder = $this->purchaseOrderForView();

        $ootDetails = $service->ootDetails($purchaseOrder);

        $this->assertCount(1, $ootDetails);
        $this->assertSame('Dextromethorphan 15 mg', $ootDetails->first()->obat->nama_obat);
        $this->assertTrue($ootDetails->first()->is_oot);
        $this->assertSame('Main Golongan: OOT Keras', $ootDetails->first()->oot_classification);
        $this->assertSame('PO-OOT-001/OOT', $purchaseOrder->oot_document_number);
    }

    public function test_oot_order_uses_four_copies_with_commercial_details_on_copies_three_and_four(): void
    {
        $service = app(SuratPesananOotService::class);
        $purchaseOrder = $this->purchaseOrderForView();
        $ootDetails = $service->ootDetails($purchaseOrder);

        $html = view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananOot', [
            'purchaseOrder' => $purchaseOrder,
            'ootDetails' => $ootDetails,
            'copyCount' => SuratPesananOotService::COPY_COUNT,
            'autoPrint' => false,
        ])->render();

        $this->assertSame(4, substr_count($html, 'class="oot-order-sheet"'));
        $this->assertSame(4, substr_count($html, 'PO-OOT-001/OOT'));
        $this->assertStringContainsString('Formulir 4', $html);
        $this->assertStringContainsString('SURAT PESANAN OBAT-OBAT TERTENTU', $html);
        $this->assertStringContainsString('Dextromethorphan 15 mg', $html);
        $this->assertStringNotContainsString('Box 10 strip', $html);
        $this->assertStringContainsString('20 Tablet (dua puluh tablet)', $html);
        $this->assertStringContainsString('Apoteker Penanggung Jawab', $html);
        $this->assertSame(2, substr_count($html, 'class="medicine-table"'));
        $this->assertSame(2, substr_count($html, 'class="medicine-table commercial-detail-table"'));
        $this->assertSame(1, substr_count($html, 'class="copy-role-badge">Rangkap Internal'));
        $this->assertSame(1, substr_count($html, 'class="copy-role-badge">Rangkap Distributor'));
        $this->assertSame(2, substr_count($html, 'class="order-quantity-column" scope="col">Qty order'));
        $this->assertSame(2, substr_count($html, 'class="order-unit-column" scope="col">Satuan order'));
        $this->assertSame(2, substr_count($html, 'class="price-column" scope="col">Harga dasar'));
        $this->assertSame(2, substr_count($html, 'class="discount-column" scope="col">Diskon'));
        $this->assertSame(2, substr_count($html, 'class="total-price-column" scope="col">Total harga'));
        $this->assertStringContainsString('Rp 18.750', $html);
        $this->assertStringContainsString('class="total-price-value">Rp 339.938', $html);
        $this->assertStringContainsString('D1 7,5%', $html);
        $this->assertStringContainsString('D2 2%', $html);
        $this->assertStringContainsString('Surat Pesanan dibuat 4 (empat) rangkap.', $html);
        $this->assertStringContainsString('Rangkap 4 dari 4', $html);
        $this->assertStringNotContainsString('Paracetamol 500 mg', $html);
    }

    private function purchaseOrderForView(): PembelianModel
    {
        $profile = (new ApotekProfile)->forceFill([
            'name' => 'Apotek Medcare Pusat',
            'address' => 'Jl. Sehat No. 1',
            'city' => 'Jakarta',
            'pharmacist_name' => 'apt. Siti Sehat, S.Farm.',
            'pharmacist_license_number' => 'SIPA-004',
        ]);
        $branch = (new BranchModel)->forceFill(['name' => 'Cabang Pusat']);
        $branch->setRelation('apotekProfile', $profile);

        $tablet = new SatuansModel(['nama' => 'Tablet']);
        $conversion = new KonversiSatuanModel;
        $conversion->setRelation('satuan', $tablet);

        $ootMedicine = $this->medicine('Dextromethorphan 15 mg', 'Box 10 strip', $tablet, true);
        $regularMedicine = $this->medicine('Paracetamol 500 mg', 'Box 10 strip', $tablet);

        $selectedDetail = (new PembelianDetailModel)->forceFill([
            'qty' => 20,
            'harga_estimasi' => 18750,
            'diskon_1' => 7.5,
            'diskon_2' => 2,
            'diskon_3' => 0,
            'is_oot' => false,
        ]);
        $selectedDetail->setRelation('obat', $ootMedicine);
        $selectedDetail->setRelation('satuanKonversi', $conversion);

        $regularDetail = (new PembelianDetailModel)->forceFill(['qty' => 10, 'is_oot' => false]);
        $regularDetail->setRelation('obat', $regularMedicine);
        $regularDetail->setRelation('satuanKonversi', $conversion);

        $purchaseOrder = (new PembelianModel)->forceFill([
            'no_po' => 'PO-OOT-001',
            'tanggal_po' => '2026-08-20',
        ]);
        $purchaseOrder->setRelation('branch', $branch);
        $purchaseOrder->setRelation('distributor', new DistributorModel([
            'nama' => 'PT Distributor OOT',
            'alamat' => 'Jl. Distribusi No. 11',
            'telepon' => '0215550404',
        ]));
        $purchaseOrder->setRelation('details', new Collection([$selectedDetail, $regularDetail]));

        return $purchaseOrder;
    }

    private function medicine(
        string $name,
        string $packaging,
        SatuansModel $unit,
        bool $isOot = false,
    ): MasterObatModel {
        $medicine = (new MasterObatModel)->forceFill([
            'nama_obat' => $name,
            'komposisi' => $name,
            'kemasan' => $packaging,
        ]);
        $classification = new GolonganModel(['kode' => 'OBK', 'nama' => 'Obat Keras']);
        $mainClassification = null;

        if ($isOot) {
            $mainClassification = new MainGolonganModel(['kode' => 'OTK', 'nama' => 'OOT Keras']);
            $mainClassification->setRelation('golongan', $classification);
        }

        $medicine->setRelation('golongan', $classification);
        $medicine->setRelation('mainGolongan', $mainClassification);
        $medicine->setRelation('subGolongan', null);
        $medicine->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $medicine->setRelation('satuan', $unit);

        return $medicine;
    }
}
