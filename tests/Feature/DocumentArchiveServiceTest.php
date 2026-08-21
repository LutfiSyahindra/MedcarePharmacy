<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\GolonganModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\SatuansModel;
use App\Models\SediaanModel;
use App\Services\Menu\Dokumen\DocumentArchiveService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class DocumentArchiveServiceTest extends TestCase
{
    public function test_archive_groups_regular_and_controlled_order_letters_without_copying_purchase_data(): void
    {
        $service = app(DocumentArchiveService::class);
        $purchaseOrder = $this->purchaseOrderWithControlledMedicines();

        $documents = $service->documentsFromPurchaseOrders(new Collection([$purchaseOrder]));

        $this->assertCount(4, $documents);
        $this->assertEqualsCanonicalizing(
            ['reguler', 'narkotika', 'psikotropika', 'prekursor'],
            $documents->pluck('type')->all(),
        );

        $regular = $documents->firstWhere('type', 'reguler');
        $this->assertSame('PO-DOC-001/REG', $regular['document_number']);
        $this->assertSame(1, $regular['item_count']);
        $this->assertSame(2, $regular['page_count']);
        $this->assertStringContainsString('surat-pesanan-reguler', $regular['print_url']);

        $narcotic = $documents->firstWhere('type', 'narkotika');
        $this->assertSame(2, $narcotic['document_count']);
        $this->assertSame(2, $narcotic['item_count']);
        $this->assertSame(10, $narcotic['page_count']);
        $this->assertSame([
            'PO-DOC-001/NAR/01',
            'PO-DOC-001/NAR/02',
        ], $narcotic['document_numbers']);

        $psychotropic = $documents->firstWhere('type', 'psikotropika');
        $this->assertSame('PO-DOC-001/PSI', $psychotropic['document_number']);
        $this->assertSame(1, $psychotropic['document_count']);
        $this->assertSame(1, $psychotropic['item_count']);

        $precursor = $documents->firstWhere('type', 'prekursor');
        $this->assertSame('PO-DOC-001/PRE', $precursor['document_number']);
        $this->assertTrue($precursor['is_ready']);
        $this->assertStringContainsString('surat-pesanan-prekursor', $precursor['print_url']);

        $this->assertSame([
            'sets' => 4,
            'documents' => 5,
            'purchase_orders' => 1,
            'reguler' => 1,
            'narkotika' => 2,
            'psikotropika' => 1,
            'prekursor' => 1,
            'oot' => 0,
            'pages' => 21,
        ], $service->summary($documents));
    }

    public function test_oot_purchase_order_only_exposes_the_oot_letter(): void
    {
        $service = app(DocumentArchiveService::class);
        $purchaseOrder = $this->purchaseOrderWithControlledMedicines(includeOot: true);

        $documents = $service->documentsFromPurchaseOrders(new Collection([$purchaseOrder]));

        $this->assertCount(1, $documents);
        $this->assertSame('oot', $documents->first()['type']);
        $this->assertSame('PO-DOC-001/OOT', $documents->first()['document_number']);
        $this->assertSame(1, $documents->first()['item_count']);
        $this->assertSame('Dextromethorphan 15 mg', $documents->first()['items'][0]['name']);
        $this->assertSame('Ditentukan manual oleh apoteker pada PO', $documents->first()['items'][0]['classification']);
        $this->assertStringContainsString('surat-pesanan-oot', $documents->first()['print_url']);

        $this->assertSame([
            'sets' => 1,
            'documents' => 1,
            'purchase_orders' => 1,
            'reguler' => 0,
            'narkotika' => 0,
            'psikotropika' => 0,
            'prekursor' => 0,
            'oot' => 1,
            'pages' => 4,
        ], $service->summary($documents));
    }

    public function test_archive_filters_type_status_and_period(): void
    {
        $service = app(DocumentArchiveService::class);
        $documents = $service->documentsFromPurchaseOrders(new Collection([
            $this->purchaseOrderWithControlledMedicines(),
        ]));

        $result = $service->filter($documents, [
            'type' => 'psikotropika',
            'status' => 'approved',
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-31',
        ]);

        $this->assertCount(1, $result);
        $this->assertSame('psikotropika', $result->first()['type']);

        $this->assertTrue($service->filter($documents, [
            'date_start' => '2026-09-01',
        ])->isEmpty());
    }

    private function purchaseOrderWithControlledMedicines(bool $includeOot = false): PembelianModel
    {
        $unit = new SatuansModel(['nama' => 'Tablet']);
        $conversion = new KonversiSatuanModel;
        $conversion->setRelation('satuan', $unit);

        $details = new Collection([
            $this->detail($this->medicine('OBK', 'Obat Keras', 'Paracetamol 500 mg', $unit), $conversion, 30),
            $this->detail($this->medicine('NAR', 'Narkotika', 'Morphine 10 mg', $unit), $conversion, 10),
            $this->detail($this->medicine('NAR', 'Narkotika', 'Fentanyl 25 mcg', $unit), $conversion, 5),
            $this->detail($this->medicine('PSI', 'Psikotropika', 'Diazepam 5 mg', $unit), $conversion, 20),
            $this->detail($this->medicine('PRE', 'Prekursor Farmasi', 'Pseudoephedrine 60 mg', $unit), $conversion, 12),
        ]);

        if ($includeOot) {
            $details->push(
                $this->detail($this->medicine('OBK', 'Obat Keras', 'Dextromethorphan 15 mg', $unit), $conversion, 8, true)
            );
        }

        $branch = (new BranchModel)->forceFill(['name' => 'Cabang Pusat']);
        $branch->setRelation('apotekProfile', null);

        $purchaseOrder = (new PembelianModel)->forceFill([
            'id' => 91,
            'no_po' => 'PO-DOC-001',
            'tanggal_po' => '2026-08-18',
            'status' => 'approved',
            'catatan' => 'Untuk stok bulan Agustus',
        ]);
        $purchaseOrder->setRelation('branch', $branch);
        $purchaseOrder->setRelation('distributor', new DistributorModel(['nama' => 'PT Distributor Sehat']));
        $purchaseOrder->setRelation('createdBy', null);
        $purchaseOrder->setRelation('approvedBy', null);
        $purchaseOrder->setRelation('details', $details);

        return $purchaseOrder;
    }

    private function medicine(string $code, string $classification, string $name, SatuansModel $unit): MasterObatModel
    {
        $medicine = (new MasterObatModel)->forceFill(['nama_obat' => $name]);
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
        $detail = (new PembelianDetailModel)->forceFill(['qty' => $quantity, 'is_oot' => $isOot]);
        $detail->setRelation('obat', $medicine);
        $detail->setRelation('satuanKonversi', $conversion);

        return $detail;
    }
}
