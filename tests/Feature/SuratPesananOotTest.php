<?php

namespace Tests\Feature;

use App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Pembelian\PembelianController;
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
use App\Services\Menu\PembelianPenerimaan\SuratPesananOotService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class SuratPesananOotTest extends TestCase
{
    public function test_purchase_detail_mapping_keeps_manual_oot_selection_aligned_per_item(): void
    {
        $request = Request::create('/purchase-orders', 'POST', [
            'obat_id' => [11, 22],
            'qty' => [2, 3],
            'harga_estimasi' => [10000, 20000],
            'diskon_1' => [0, 0],
            'diskon_2' => [0, 0],
            'diskon_3' => [0, 0],
            'satuan_id' => [101, 202],
            'is_oot' => [1, 0],
        ]);
        $method = new ReflectionMethod(PembelianController::class, 'purchaseDetailRows');
        $method->setAccessible(true);

        $rows = $method->invoke(app(PembelianController::class), $request);

        $this->assertTrue($rows[0]['is_oot']);
        $this->assertFalse($rows[1]['is_oot']);
        $this->assertSame(11, $rows[0]['obat_id']);
        $this->assertSame(22, $rows[1]['obat_id']);
    }

    public function test_oot_items_follow_the_pharmacist_selection_instead_of_medicine_classification(): void
    {
        $service = app(SuratPesananOotService::class);
        $purchaseOrder = $this->purchaseOrderForView();

        $ootDetails = $service->ootDetails($purchaseOrder);

        $this->assertCount(1, $ootDetails);
        $this->assertSame('Dextromethorphan 15 mg', $ootDetails->first()->obat->nama_obat);
        $this->assertTrue($ootDetails->first()->is_oot);
        $this->assertSame('Ditentukan manual oleh apoteker pada PO', $ootDetails->first()->oot_classification);
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
        $this->assertStringContainsString('Box 10 strip', $html);
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

        $ootMedicine = $this->medicine('Dextromethorphan 15 mg', 'Box 10 strip', $tablet);
        $regularMedicine = $this->medicine('Paracetamol 500 mg', 'Box 10 strip', $tablet);

        $selectedDetail = (new PembelianDetailModel)->forceFill([
            'qty' => 20,
            'harga_estimasi' => 18750,
            'diskon_1' => 7.5,
            'diskon_2' => 2,
            'diskon_3' => 0,
            'is_oot' => true,
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

    private function medicine(string $name, string $packaging, SatuansModel $unit): MasterObatModel
    {
        $medicine = (new MasterObatModel)->forceFill([
            'nama_obat' => $name,
            'komposisi' => $name,
            'kemasan' => $packaging,
        ]);
        $medicine->setRelation('golongan', new GolonganModel(['kode' => 'OBK', 'nama' => 'Obat Keras']));
        $medicine->setRelation('mainGolongan', null);
        $medicine->setRelation('subGolongan', null);
        $medicine->setRelation('sediaan', new SediaanModel(['nama' => 'Tablet']));
        $medicine->setRelation('satuan', $unit);

        return $medicine;
    }
}
