<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PenerimaanPrintTest extends TestCase
{
    public function test_professional_receipt_document_contains_operational_and_financial_details(): void
    {
        [$receipt, $medicine] = $this->receiptForView();

        $html = view('medcare.menu.pembelianPenerimaan.penerimaan.print', [
            'penerimaan' => $receipt,
            'autoPrint' => false,
        ])->render();

        $this->assertStringContainsString('PENERIMAAN BARANG', $html);
        $this->assertStringContainsString('@page { size: A4 portrait;', $html);
        $this->assertStringContainsString('PB-PRINT-001', $html);
        $this->assertStringContainsString('PO-PRINT-001', $html);
        $this->assertStringContainsString('INV-PRINT-001', $html);
        $this->assertStringContainsString('Apotek Medcare Harmoni', $html);
        $this->assertStringContainsString('PT Sehat Distribusi', $html);
        $this->assertStringContainsString($medicine->nama_obat, $html);
        $this->assertStringContainsString('BATCH-PRINT-01', $html);
        $this->assertStringContainsString('Total Tagihan', $html);
        $this->assertStringContainsString('Petugas Penerima', $html);
        $this->assertStringContainsString('Apoteker / Penanggung Jawab', $html);
        $this->assertStringNotContainsString('window.setTimeout(function () { window.print(); }', $html);
    }

    public function test_auto_print_mode_and_named_route_are_available(): void
    {
        [$receipt] = $this->receiptForView();

        $html = view('medcare.menu.pembelianPenerimaan.penerimaan.print', [
            'penerimaan' => $receipt,
            'autoPrint' => true,
        ])->render();

        $route = Route::getRoutes()->getByName('penerimaan.print');

        $this->assertNotNull($route);
        $this->assertSame('GET', $route->methods()[0]);
        $this->assertStringContainsString('window.setTimeout(function () { window.print(); }', $html);
    }

    /**
     * @return array{0: PenerimaanBarangModel, 1: MasterObatModel}
     */
    private function receiptForView(): array
    {
        $profile = (new ApotekProfile)->forceFill([
            'name' => 'Apotek Medcare Harmoni',
            'address' => 'Jl. Harmoni Sehat No. 10',
            'city' => 'Jakarta',
            'phone' => '021-555-0101',
            'pharmacist_name' => 'apt. Sinta Sehat, S.Farm.',
            'pharmacist_license_number' => 'SIPA-PRINT-001',
        ]);
        $branch = (new BranchModel)->forceFill([
            'name' => 'Cabang Harmoni',
            'address' => 'Jl. Harmoni No. 10',
            'phone' => '021-555-0101',
            'email' => 'harmoni@medcare.test',
        ]);
        $branch->setRelation('apotekProfile', $profile);

        $supplier = new DistributorModel([
            'nama' => 'PT Sehat Distribusi',
            'alamat' => 'Jl. Logistik No. 5',
            'telepon' => '021-555-0202',
            'email' => 'order@sehat.test',
        ]);
        $unit = new SatuansModel(['nama' => 'Box']);
        $medicine = (new MasterObatModel)->forceFill([
            'kode_obat' => 'OBT-PRINT-001',
            'nama_obat' => 'Paracetamol 500 mg',
        ]);
        $medicine->setRelation('satuan', $unit);

        $purchaseOrder = (new PembelianModel)->forceFill([
            'no_po' => 'PO-PRINT-001',
            'tanggal_po' => '2026-09-20',
        ]);
        $purchaseOrder->setRelation('branch', $branch);
        $purchaseOrder->setRelation('distributor', $supplier);

        $detail = (new PenerimaanBarangDetailModel)->forceFill([
            'qty_po' => 10,
            'qty_diterima' => 10,
            'qty_diterima_stok' => 10,
            'konversi_satuan' => 1,
            'satuan_beli' => 'Box',
            'satuan_stok' => 'Box',
            'no_batch' => 'BATCH-PRINT-01',
            'expired_date' => '2028-09-30',
            'harga_beli' => 10000,
            'diskon_1' => 5,
            'diskon_2' => 0,
            'diskon_3' => 0,
            'ppn' => 11,
            'total' => 105450,
        ]);
        $detail->setRelation('obat', $medicine);
        $detail->setRelation('purchaseOrderDetail', null);

        $operator = (new User)->forceFill(['name' => 'Rina Penerima']);
        $receipt = (new PenerimaanBarangModel)->forceFill([
            'nomor_penerimaan' => 'PB-PRINT-001',
            'nomor_faktur' => 'INV-PRINT-001',
            'nomor_surat_jalan' => 'SJ-PRINT-001',
            'tanggal_penerimaan' => '2026-09-26',
            'tanggal_faktur' => '2026-09-25',
            'tanggal_jatuh_tempo' => '2026-10-25',
            'total_barang' => 1,
            'total_qty' => 10,
            'subtotal' => 100000,
            'total_diskon' => 5000,
            'diskon' => 5000,
            'total_ppn' => 10450,
            'pajak' => 10450,
            'biaya_lain' => 2000,
            'grand_total' => 107450,
            'total_faktur' => 107450,
            'status' => 'posted',
            'status_pembayaran' => 'belum_dibayar',
            'jumlah_dibayar' => 0,
            'sisa_hutang' => 107450,
            'supplier_compensation_discount' => 0,
            'catatan' => 'Kemasan diterima dalam kondisi baik.',
            'posted_at' => '2026-09-26 10:30:00',
        ]);
        $receipt->setRelation('purchaseOrder', $purchaseOrder);
        $receipt->setRelation('distributor', $supplier);
        $receipt->setRelation('details', new Collection([$detail]));
        $receipt->setRelation('createdBy', $operator);
        $receipt->setRelation('postedBy', $operator);
        $receipt->setRelation('cancelledBy', null);

        return [$receipt, $medicine];
    }
}
