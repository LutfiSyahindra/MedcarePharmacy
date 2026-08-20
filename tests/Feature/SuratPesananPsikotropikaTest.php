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
use App\Services\Menu\PembelianPenerimaan\SuratPesananPsikotropikaService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class SuratPesananPsikotropikaTest extends TestCase
{
    public function test_psychotropic_is_detected_at_golongan_main_and_sub_golongan_levels(): void
    {
        $service = app(SuratPesananPsikotropikaService::class);

        $this->assertTrue($service->isPsychotropicDrug($this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'PIS', 'nama' => 'Psikotropika'])
        )));

        $this->assertTrue($service->isPsychotropicDrug($this->medicineWithClassifications(
            main: new MainGolonganModel(['kode' => 'PSI-2', 'nama' => 'Psikotropika Golongan II'])
        )));

        $this->assertTrue($service->isPsychotropicDrug($this->medicineWithClassifications(
            sub: new SubGolonganModel(['kode' => 'SUB-PSI', 'nama' => 'Turunan Psikotropika'])
        )));

        $main = new MainGolonganModel(['kode' => 'BENZO', 'nama' => 'Benzodiazepin']);
        $main->setRelation('golongan', new GolonganModel(['kode' => 'PIS', 'nama' => 'Psikotropika']));
        $this->assertTrue($service->isPsychotropicDrug($this->medicineWithClassifications(main: $main)));

        $this->assertFalse($service->isPsychotropicDrug($this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'OBK', 'nama' => 'Obat Keras'])
        )));
    }

    public function test_psychotropic_order_combines_all_matching_items_in_one_letter_with_three_copies(): void
    {
        $service = app(SuratPesananPsikotropikaService::class);
        $purchaseOrder = $this->purchaseOrderForView();
        $psychotropicDetails = $service->psychotropicDetails($purchaseOrder);

        $html = view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananPsikotropika', [
            'purchaseOrder' => $purchaseOrder,
            'psychotropicDetails' => $psychotropicDetails,
            'copyCount' => SuratPesananPsikotropikaService::COPY_COUNT,
            'autoPrint' => false,
        ])->render();

        $this->assertCount(2, $psychotropicDetails);
        $this->assertSame(3, substr_count($html, 'class="psychotropic-order-sheet"'));
        $this->assertSame(3, substr_count($html, 'PO-PSI-001/PSI'));
        $this->assertStringContainsString('Formulir 2', $html);
        $this->assertStringContainsString('SURAT PESANAN PSIKOTROPIKA', $html);
        $this->assertStringContainsString('class="medicine-table"', $html);
        $this->assertStringContainsString('class="name-column" scope="col">Nama obat', $html);
        $this->assertStringContainsString('class="preparation-column" scope="col">Bentuk sediaan', $html);
        $this->assertStringContainsString('class="strength-column" scope="col">Kekuatan/potensi', $html);
        $this->assertStringContainsString('Diazepam 5 mg', $html);
        $this->assertStringContainsString('Clobazam 10 mg', $html);
        $this->assertStringContainsString('20 Tablet (dua puluh tablet)', $html);
        $this->assertStringContainsString('10 Tablet (sepuluh tablet)', $html);
        $this->assertStringContainsString('Rangkap 3 dari 3', $html);
        $this->assertStringContainsString('Surat Pesanan dibuat sekurang-kurangnya 3 (tiga) rangkap.', $html);
        $this->assertStringNotContainsString('Paracetamol 500 mg', $html);
        $this->assertStringNotContainsString('Satu surat pesanan hanya berlaku', $html);
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
            'pharmacist_license_number' => 'SIPA-002',
        ]);
        $branch = (new BranchModel)->forceFill(['name' => 'Cabang Pusat']);
        $branch->setRelation('apotekProfile', $profile);

        $tablet = new SatuansModel(['nama' => 'Tablet']);
        $conversion = new KonversiSatuanModel;
        $conversion->setRelation('satuan', $tablet);

        $psychotropicClassification = new GolonganModel(['kode' => 'PIS', 'nama' => 'Psikotropika']);
        $diazepam = $this->medicineWithClassifications(golongan: $psychotropicClassification);
        $diazepam->forceFill(['nama_obat' => 'Diazepam 5 mg', 'komposisi' => 'Diazepam 5 mg']);
        $diazepam->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $diazepam->setRelation('satuan', $tablet);

        $clobazam = $this->medicineWithClassifications(
            main: new MainGolonganModel(['kode' => 'PSI-IV', 'nama' => 'Psikotropika Golongan IV'])
        );
        $clobazam->forceFill(['nama_obat' => 'Clobazam 10 mg', 'komposisi' => 'Clobazam 10 mg']);
        $clobazam->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $clobazam->setRelation('satuan', $tablet);

        $regular = $this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'OBK', 'nama' => 'Obat Keras'])
        );
        $regular->forceFill(['nama_obat' => 'Paracetamol 500 mg']);
        $regular->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $regular->setRelation('satuan', $tablet);

        $diazepamDetail = $this->detail($diazepam, $conversion, 20);
        $clobazamDetail = $this->detail($clobazam, $conversion, 10);
        $regularDetail = $this->detail($regular, $conversion, 12);

        $purchaseOrder = (new PembelianModel)->forceFill([
            'no_po' => 'PO-PSI-001',
            'tanggal_po' => '2026-08-18',
        ]);
        $purchaseOrder->setRelation('branch', $branch);
        $purchaseOrder->setRelation('distributor', new DistributorModel([
            'nama' => 'PT Distributor Psikotropika',
            'alamat' => 'Jl. Distribusi No. 9',
            'telepon' => '0215550202',
        ]));
        $purchaseOrder->setRelation('details', new Collection([
            $diazepamDetail,
            $clobazamDetail,
            $regularDetail,
        ]));

        return $purchaseOrder;
    }

    private function detail(
        MasterObatModel $medicine,
        KonversiSatuanModel $conversion,
        int $quantity
    ): PembelianDetailModel {
        $detail = (new PembelianDetailModel)->forceFill(['qty' => $quantity]);
        $detail->setRelation('obat', $medicine);
        $detail->setRelation('satuanKonversi', $conversion);

        return $detail;
    }
}
