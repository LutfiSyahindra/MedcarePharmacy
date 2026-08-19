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
use App\Models\SubGolonganModel;
use App\Services\Menu\PembelianPenerimaan\SuratPesananNarkotikaService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class SuratPesananNarkotikaTest extends TestCase
{
    public function test_narcotic_is_detected_at_golongan_main_and_sub_golongan_levels(): void
    {
        $service = app(SuratPesananNarkotikaService::class);

        $this->assertTrue($service->isNarcoticDrug($this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'NAR', 'nama' => 'Narkotika'])
        )));

        $this->assertTrue($service->isNarcoticDrug($this->medicineWithClassifications(
            main: new MainGolonganModel(['kode' => 'NAR-1', 'nama' => 'Narkotika Golongan I'])
        )));

        $this->assertTrue($service->isNarcoticDrug($this->medicineWithClassifications(
            sub: new SubGolonganModel(['kode' => 'SUB-NAR', 'nama' => 'Turunan Narkotika'])
        )));

        $this->assertFalse($service->isNarcoticDrug($this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'OBK', 'nama' => 'Obat Keras'])
        )));
    }

    public function test_narcotic_order_view_makes_one_letter_per_item_in_three_copies(): void
    {
        $service = app(SuratPesananNarkotikaService::class);
        $purchaseOrder = $this->purchaseOrderForView();
        $narcoticDetails = $service->narcoticDetails($purchaseOrder);

        $html = view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananNarkotika', [
            'purchaseOrder' => $purchaseOrder,
            'narcoticDetails' => $narcoticDetails,
            'copyCount' => SuratPesananNarkotikaService::COPY_COUNT,
            'autoPrint' => false,
        ])->render();

        $this->assertCount(1, $narcoticDetails);
        $this->assertSame(3, substr_count($html, 'class="narcotic-order-sheet"'));
        $this->assertStringContainsString('SURAT PESANAN NARKOTIKA', $html);
        $this->assertStringContainsString('PO-NAR-001/NAR/01', $html);
        $this->assertStringContainsString('Morphine 10 mg', $html);
        $this->assertStringContainsString('25 Tablet (dua puluh lima tablet)', $html);
        $this->assertStringContainsString('Apotek Medcare Pusat', $html);
        $this->assertStringContainsString('apt. Siti Sehat, S.Farm.', $html);
        $this->assertStringContainsString('SIPA-001', $html);
        $this->assertStringContainsString('Rangkap 3 dari 3', $html);
        $this->assertStringNotContainsString('Paracetamol 500 mg', $html);
    }

    public function test_quantity_is_written_in_indonesian_words(): void
    {
        $service = app(SuratPesananNarkotikaService::class);

        $this->assertSame('nol', $service->quantityInWords(0));
        $this->assertSame('sebelas', $service->quantityInWords(11));
        $this->assertSame('seratus dua puluh lima', $service->quantityInWords(125));
        $this->assertSame('seribu satu', $service->quantityInWords(1001));
    }

    private function medicineWithClassifications(
        ?GolonganModel $golongan = null,
        ?MainGolonganModel $main = null,
        ?SubGolonganModel $sub = null,
    ): MasterObatModel {
        $medicine = new MasterObatModel;
        $medicine->setRelation('golongan', $golongan);
        $medicine->setRelation('mainGolongan', $main);
        $medicine->setRelation('subGolongan', $sub);

        if ($main && ! $main->relationLoaded('golongan')) {
            $main->setRelation('golongan', null);
        }

        if ($sub && ! $sub->relationLoaded('mainGolongan')) {
            $sub->setRelation('mainGolongan', null);
        }

        return $medicine;
    }

    private function purchaseOrderForView(): PembelianModel
    {
        $profile = (new ApotekProfile)->forceFill([
            'name' => 'Apotek Medcare Pusat',
            'address' => 'Jl. Sehat No. 1',
            'city' => 'Jakarta',
            'pharmacist_name' => 'apt. Siti Sehat, S.Farm.',
            'pharmacist_license_number' => 'SIPA-001',
        ]);
        $branch = (new BranchModel)->forceFill(['name' => 'Cabang Pusat']);
        $branch->setRelation('apotekProfile', $profile);

        $tablet = new SatuansModel(['nama' => 'Tablet']);
        $conversion = new KonversiSatuanModel;
        $conversion->setRelation('satuan', $tablet);

        $narcotic = $this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'NAR', 'nama' => 'Narkotika'])
        );
        $narcotic->forceFill([
            'nama_obat' => 'Morphine 10 mg',
            'komposisi' => 'Morphine sulfate 10 mg',
        ]);
        $narcotic->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $narcotic->setRelation('satuan', $tablet);

        $regular = $this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'OBK', 'nama' => 'Obat Keras'])
        );
        $regular->forceFill(['nama_obat' => 'Paracetamol 500 mg']);
        $regular->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $regular->setRelation('satuan', $tablet);

        $narcoticDetail = (new PembelianDetailModel)->forceFill(['qty' => 25]);
        $narcoticDetail->setRelation('obat', $narcotic);
        $narcoticDetail->setRelation('satuanKonversi', $conversion);

        $regularDetail = (new PembelianDetailModel)->forceFill(['qty' => 10]);
        $regularDetail->setRelation('obat', $regular);
        $regularDetail->setRelation('satuanKonversi', $conversion);

        $purchaseOrder = (new PembelianModel)->forceFill([
            'no_po' => 'PO-NAR-001',
            'tanggal_po' => '2026-08-18',
        ]);
        $purchaseOrder->setRelation('branch', $branch);
        $purchaseOrder->setRelation('distributor', new DistributorModel([
            'nama' => 'PT Distributor Sehat',
            'alamat' => 'Jl. Distribusi No. 2',
            'telepon' => '0215550101',
        ]));
        $purchaseOrder->setRelation('details', new Collection([$narcoticDetail, $regularDetail]));

        return $purchaseOrder;
    }
}
