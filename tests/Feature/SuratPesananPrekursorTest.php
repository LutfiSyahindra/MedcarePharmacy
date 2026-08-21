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
use App\Services\Menu\PembelianPenerimaan\SuratPesananPrekursorService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class SuratPesananPrekursorTest extends TestCase
{
    public function test_precursor_is_detected_at_golongan_main_and_sub_golongan_levels(): void
    {
        $service = app(SuratPesananPrekursorService::class);

        $this->assertTrue($service->isPrecursorDrug($this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'PRE', 'nama' => 'Prekursor Farmasi'])
        )));

        $this->assertTrue($service->isPrecursorDrug($this->medicineWithClassifications(
            main: new MainGolonganModel(['kode' => 'PRK', 'nama' => 'Prekursor Keras'])
        )));

        $this->assertTrue($service->isPrecursorDrug($this->medicineWithClassifications(
            sub: new SubGolonganModel(['kode' => 'PR', 'nama' => 'Prekursor'])
        )));

        $main = new MainGolonganModel(['kode' => 'EFEDRIN', 'nama' => 'Golongan Efedrin']);
        $main->setRelation('golongan', new GolonganModel(['kode' => 'PRE', 'nama' => 'Prekursor Farmasi']));
        $this->assertTrue($service->isPrecursorDrug($this->medicineWithClassifications(main: $main)));

        $this->assertFalse($service->isPrecursorDrug($this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'OBK', 'nama' => 'Obat Keras'])
        )));
    }

    public function test_precursor_order_uses_four_copies_with_commercial_details_on_copies_three_and_four(): void
    {
        $service = app(SuratPesananPrekursorService::class);
        $purchaseOrder = $this->purchaseOrderForView();
        $precursorDetails = $service->precursorDetails($purchaseOrder);

        $html = view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananPrekursor', [
            'purchaseOrder' => $purchaseOrder,
            'precursorDetails' => $precursorDetails,
            'copyCount' => SuratPesananPrekursorService::COPY_COUNT,
            'autoPrint' => false,
        ])->render();

        $this->assertCount(2, $precursorDetails);
        $this->assertSame(4, substr_count($html, 'class="precursor-order-sheet"'));
        $this->assertSame(4, substr_count($html, 'PO-PRE-001/PRE'));
        $this->assertStringContainsString('Formulir 3', $html);
        $this->assertStringContainsString('SURAT PESANAN OBAT/BAHAN OBAT/PREKURSOR FARMASI*', $html);
        $this->assertStringContainsString('class="medicine-table"', $html);
        $this->assertStringContainsString('class="name-column" scope="col">Nama obat', $html);
        $this->assertStringContainsString('class="preparation-column" scope="col">Bentuk sediaan', $html);
        $this->assertStringContainsString('class="strength-column" scope="col">Kekuatan/potensi', $html);
        $this->assertStringContainsString('class="packaging-column" scope="col">Isi kemasan', $html);
        $this->assertStringContainsString('Pseudoephedrine 60 mg', $html);
        $this->assertStringContainsString('Ephedrine 25 mg', $html);
        $this->assertStringContainsString('Isi kemasan', $html);
        $this->assertStringContainsString('Box 10 strip', $html);
        $this->assertStringContainsString('20 Tablet (dua puluh tablet)', $html);
        $this->assertStringContainsString('Apoteker/Tenaga Teknis Kefarmasian', $html);
        $this->assertStringContainsString('No. SIPA/SIKTTK', $html);
        $this->assertSame(2, substr_count($html, 'class="medicine-table"'));
        $this->assertSame(2, substr_count($html, 'class="medicine-table commercial-detail-table"'));
        $this->assertSame(1, substr_count($html, 'class="copy-role-badge">Rangkap Internal'));
        $this->assertSame(1, substr_count($html, 'class="copy-role-badge">Rangkap Distributor'));
        $this->assertSame(2, substr_count($html, 'class="order-quantity-column" scope="col">Qty order'));
        $this->assertSame(2, substr_count($html, 'class="order-unit-column" scope="col">Satuan order'));
        $this->assertSame(2, substr_count($html, 'class="price-column" scope="col">Harga dasar'));
        $this->assertSame(2, substr_count($html, 'class="discount-column" scope="col">Diskon'));
        $this->assertSame(2, substr_count($html, 'class="total-price-column" scope="col">Total harga'));
        $this->assertStringContainsString('Rp 12.500', $html);
        $this->assertStringContainsString('class="total-price-value">Rp 231.563', $html);
        $this->assertStringContainsString('class="total-price-value">Rp 115.781', $html);
        $this->assertStringContainsString('D1 5%', $html);
        $this->assertStringContainsString('D2 2,5%', $html);
        $this->assertStringContainsString('Surat Pesanan dibuat 4 (empat) rangkap.', $html);
        $this->assertStringContainsString('Rangkap 4 dari 4', $html);
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
            'pharmacist_license_number' => 'SIPA-003',
        ]);
        $branch = (new BranchModel)->forceFill(['name' => 'Cabang Pusat']);
        $branch->setRelation('apotekProfile', $profile);

        $tablet = new SatuansModel(['nama' => 'Tablet']);
        $conversion = new KonversiSatuanModel;
        $conversion->setRelation('satuan', $tablet);

        $pseudoephedrine = $this->medicineWithClassifications(
            main: new MainGolonganModel(['kode' => 'PRK', 'nama' => 'Prekursor Keras'])
        );
        $pseudoephedrine->forceFill([
            'nama_obat' => 'Pseudoephedrine 60 mg',
            'komposisi' => 'Pseudoephedrine HCl 60 mg',
            'kemasan' => 'Box 10 strip',
        ]);
        $pseudoephedrine->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $pseudoephedrine->setRelation('satuan', $tablet);

        $ephedrine = $this->medicineWithClassifications(
            sub: new SubGolonganModel(['kode' => 'PR', 'nama' => 'Prekursor'])
        );
        $ephedrine->forceFill([
            'nama_obat' => 'Ephedrine 25 mg',
            'komposisi' => 'Ephedrine HCl 25 mg',
            'kemasan' => 'Box 10 strip',
        ]);
        $ephedrine->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $ephedrine->setRelation('satuan', $tablet);

        $regular = $this->medicineWithClassifications(
            golongan: new GolonganModel(['kode' => 'OBK', 'nama' => 'Obat Keras'])
        );
        $regular->forceFill(['nama_obat' => 'Paracetamol 500 mg']);
        $regular->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $regular->setRelation('satuan', $tablet);

        $purchaseOrder = (new PembelianModel)->forceFill([
            'no_po' => 'PO-PRE-001',
            'tanggal_po' => '2026-08-18',
        ]);
        $purchaseOrder->setRelation('branch', $branch);
        $purchaseOrder->setRelation('distributor', new DistributorModel([
            'nama' => 'PT Distributor Prekursor',
            'alamat' => 'Jl. Distribusi No. 10',
            'telepon' => '0215550303',
        ]));
        $purchaseOrder->setRelation('details', new Collection([
            $this->detail($pseudoephedrine, $conversion, 20),
            $this->detail($ephedrine, $conversion, 10),
            $this->detail($regular, $conversion, 12),
        ]));

        return $purchaseOrder;
    }

    private function detail(
        MasterObatModel $medicine,
        KonversiSatuanModel $conversion,
        int $quantity
    ): PembelianDetailModel {
        $detail = (new PembelianDetailModel)->forceFill([
            'qty' => $quantity,
            'harga_estimasi' => 12500,
            'diskon_1' => 5,
            'diskon_2' => 2.5,
            'diskon_3' => 0,
        ]);
        $detail->setRelation('obat', $medicine);
        $detail->setRelation('satuanKonversi', $conversion);

        return $detail;
    }
}
