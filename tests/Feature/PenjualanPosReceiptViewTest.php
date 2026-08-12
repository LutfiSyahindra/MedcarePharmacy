<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PenjualanPosReceiptViewTest extends TestCase
{
    public function test_compound_receipt_has_professional_patient_and_grouped_pharmacy_copies(): void
    {
        $transaction = (new PenjualanTransactionModel)->forceFill([
            'nomor_transaksi' => 'POS-RACIKAN-VIEW-001',
            'tanggal_transaksi' => Carbon::parse('2026-07-22 10:30:00'),
            'jenis_transaksi' => 'penjualan_racikan',
            'payment_status' => 'paid',
            'customer_name' => 'Siti Aminah',
            'nomor_resep' => 'RSP-VIEW-001',
            'tanggal_resep' => Carbon::parse('2026-07-22'),
            'dokter_name' => 'dr. Andini',
            'asal_resep' => 'Klinik Medcare',
            'subtotal_gross' => 4000,
            'diskon_item_total' => 0,
            'diskon_transaksi_nominal' => 0,
            'pajak_total' => 0,
            'embalase' => 8000,
            'grand_total' => 12000,
            'total_bayar' => 12000,
            'sisa_tagihan' => 0,
            'kembalian' => 0,
        ]);

        $transaction->setRelation('details', collect([
            $this->compoundDetail('R/ 1', 'Kapsul', 'Paracetamol 500 mg', 0.6, 1, 1000, 5000),
            $this->compoundDetail('R/ 1', 'Kapsul', 'CTM 4 mg', 1.2, 2, 1000, 5000),
            $this->compoundDetail('R/ 2', 'Pulveres', 'Vitamin C 500 mg', 1.2, 2, 2000, 3000),
        ]));
        $transaction->setRelation('payments', collect());
        $transaction->setRelation('branch', (new BranchModel)->forceFill([
            'name' => 'Apotek Medcare',
            'address' => 'Jl. Sehat No. 1',
            'phone' => '021000001',
        ]));
        $transaction->setRelation('createdBy', (new User)->forceFill(['name' => 'Kasir Uji']));
        $transaction->setRelation('completedBy', null);

        $html = view('medcare.menu.penjualan.pos.receipt', [
            'transaction' => $transaction,
            'transactionTypes' => ['penjualan_racikan' => 'Resep Racikan'],
            'paymentMethods' => [],
            'apotekProfile' => null,
            'embedded' => true,
            'autoPrint' => false,
        ])->render();

        $pharmacyStart = strpos($html, '<main class="receipt receipt-pharmacy-copy">');
        $this->assertNotFalse($pharmacyStart);

        $patientHtml = substr($html, 0, $pharmacyStart);
        $pharmacyHtml = substr($html, $pharmacyStart);

        $this->assertStringContainsString('Salinan Pasien', $patientHtml);
        $this->assertStringContainsString('Bukti Pembayaran Resep', $patientHtml);
        $this->assertStringContainsString('Racikan 1 · Kapsul', $patientHtml);
        $this->assertStringContainsString('Racikan 2 · Pulveres', $patientHtml);
        $this->assertStringNotContainsString('Paracetamol 500 mg', $patientHtml);
        $this->assertStringNotContainsString('Vitamin C 500 mg', $patientHtml);

        $this->assertStringContainsString('Salinan Apotek', $pharmacyHtml);
        $this->assertStringContainsString('Lembar Peracikan', $pharmacyHtml);
        $this->assertSame(2, substr_count($pharmacyHtml, 'class="pharmacy-compound-group"'));
        $this->assertStringContainsString('0,60 tablet', $pharmacyHtml);
        $this->assertStringContainsString('1,20 tablet', $pharmacyHtml);
        $this->assertTrue(
            strpos($pharmacyHtml, 'Racikan 1') < strpos($pharmacyHtml, 'Paracetamol 500 mg')
            && strpos($pharmacyHtml, 'CTM 4 mg') < strpos($pharmacyHtml, 'Racikan 2')
            && strpos($pharmacyHtml, 'Racikan 2') < strpos($pharmacyHtml, 'Vitamin C 500 mg')
        );
    }

    public function test_compound_labels_use_the_same_thermal_print_configuration_as_receipts(): void
    {
        $transaction = (new PenjualanTransactionModel)->forceFill([
            'nomor_transaksi' => 'POS-LABEL-VIEW-001',
            'tanggal_transaksi' => Carbon::parse('2026-07-22 11:00:00'),
            'jenis_transaksi' => 'penjualan_racikan',
            'customer_name' => 'Siti Aminah',
            'nomor_resep' => 'RSP-LABEL-001',
            'dokter_name' => 'dr. Andini',
        ]);
        $transaction->setRelation('details', collect([
            $this->compoundDetail('R/ 1', 'Kapsul', 'Paracetamol 500 mg', 0.6, 1, 1000, 5000),
            $this->compoundDetail('R/ 1', 'Kapsul', 'CTM 4 mg', 1.2, 2, 1000, 5000),
            $this->compoundDetail('R/ 2', 'Suspensi', 'Vitamin C 500 mg', 1.2, 2, 2000, 3000),
        ]));
        $transaction->setRelation('branch', (new BranchModel)->forceFill([
            'name' => 'Cabang Medcare Uji',
            'address' => 'Jl. Sehat No. 1',
            'phone' => '021000001',
        ]));

        $profile = (new ApotekProfile)->forceFill([
            'name' => 'Apotek Medcare Profesional',
            'logo_path' => 'apotek-logos/logo-branch-uji.png',
            'phone' => '0215550123',
            'whatsapp' => '6281211112222',
            'address' => 'Jl. Farmasi No. 8',
        ]);

        $viewData = [
            'transaction' => $transaction,
            'apotekProfile' => $profile,
            'embedded' => true,
            'autoPrint' => false,
        ];
        $html = view('medcare.menu.penjualan.pos.labels', $viewData)->render();

        $this->assertSame(2, substr_count($html, 'class="label-sheet"'));
        $this->assertStringContainsString('size: 80mm auto;', $html);
        $this->assertStringContainsString('width: 80mm;', $html);
        $this->assertStringContainsString('page-break-before: always;', $html);
        $this->assertStringContainsString('Apotek Medcare Profesional', $html);
        $this->assertStringContainsString('storage/apotek-logos/logo-branch-uji.png', $html);
        $this->assertStringContainsString('Logo branch Apotek Medcare Profesional', $html);
        $this->assertStringContainsString('Siti Aminah', $html);
        $this->assertStringContainsString('Racikan 1', $html);
        $this->assertStringContainsString('Kapsul', $html);
        $this->assertStringContainsString('Racikan 2', $html);
        $this->assertStringContainsString('Suspensi', $html);
        $this->assertStringContainsString('Kocok dahulu', $html);
        $this->assertStringContainsString('0215550123', $html);
        $this->assertStringNotContainsString('6281211112222', $html);
        $this->assertStringNotContainsString('Jl. Farmasi No. 8', $html);
        $this->assertStringNotContainsString('Paracetamol 500 mg', $html);
        $this->assertStringNotContainsString('Vitamin C 500 mg', $html);

        $standaloneHtml = view('medcare.menu.penjualan.pos.labels', array_replace($viewData, [
            'embedded' => false,
            'autoPrint' => true,
        ]))->render();

        $this->assertStringContainsString('Cetak Etiket', $standaloneHtml);
        $this->assertStringContainsString('onclick="window.print()"', $standaloneHtml);
        $this->assertStringContainsString('window.focus();', $standaloneHtml);
        $this->assertStringContainsString('window.print();', $standaloneHtml);
        $this->assertStringContainsString('}, 100);', $standaloneHtml);
        $this->assertStringNotContainsString('labelSizeSelect', $standaloneHtml);
        $this->assertStringNotContainsString('Buka PDF cetak', $standaloneHtml);
    }

    public function test_non_compound_labels_create_one_sheet_per_medicine_without_address_or_whatsapp(): void
    {
        $transaction = (new PenjualanTransactionModel)->forceFill([
            'nomor_transaksi' => 'POS-RESEP-VIEW-001',
            'tanggal_transaksi' => Carbon::parse('2026-07-23 09:00:00'),
            'jenis_transaksi' => 'penjualan_resep',
            'customer_name' => 'Budi Santoso',
            'nomor_resep' => 'RSP-NON-RACIKAN-001',
            'dokter_name' => 'dr. Maya',
        ]);
        $transaction->setRelation('details', collect([
            (new PenjualanTransactionDetailModel)->forceFill([
                'nama_obat' => 'Amoxicillin 500 mg',
                'satuan_jual' => 'Kapsul',
                'qty_jual' => 10,
                'aturan_pakai' => '3 x sehari 1 kapsul',
                'waktu_konsumsi' => 'sesudah_makan',
                'durasi_hari' => 3,
                'keterangan' => 'Harus dihabiskan',
            ]),
            (new PenjualanTransactionDetailModel)->forceFill([
                'nama_obat' => 'Cetirizine 10 mg',
                'satuan_jual' => 'Tablet',
                'qty_jual' => 5,
                'aturan_pakai' => '1 x sehari 1 tablet',
                'waktu_konsumsi' => 'sesudah_makan',
                'durasi_hari' => 5,
            ]),
        ]));
        $transaction->setRelation('branch', (new BranchModel)->forceFill([
            'name' => 'Cabang Medcare Uji',
            'address' => 'Alamat cabang tidak boleh tampil',
            'phone' => '021000001',
        ]));

        $profile = (new ApotekProfile)->forceFill([
            'name' => 'Apotek Non Racikan',
            'phone' => '021998877',
            'whatsapp' => '6281299998888',
            'address' => 'Jl. Etiket Non Racikan No. 5',
        ]);

        $html = view('medcare.menu.penjualan.pos.labels', [
            'transaction' => $transaction,
            'apotekProfile' => $profile,
            'embedded' => true,
            'autoPrint' => false,
        ])->render();

        $this->assertSame(2, substr_count($html, 'class="label-sheet"'));
        $this->assertStringContainsString('Etiket Non Racikan', $html);
        $this->assertStringContainsString('OBAT RESEP', $html);
        $this->assertStringContainsString('Budi Santoso', $html);
        $this->assertStringContainsString('Amoxicillin 500 mg', $html);
        $this->assertStringContainsString('10 kapsul', $html);
        $this->assertStringContainsString('3 x sehari 1 kapsul', $html);
        $this->assertStringContainsString('Cetirizine 10 mg', $html);
        $this->assertStringContainsString('5 tablet', $html);
        $this->assertStringContainsString('1 x sehari 1 tablet', $html);
        $this->assertStringContainsString('021998877', $html);
        $this->assertStringNotContainsString('6281299998888', $html);
        $this->assertStringNotContainsString('Jl. Etiket Non Racikan No. 5', $html);
        $this->assertStringNotContainsString('Alamat cabang tidak boleh tampil', $html);
    }

    private function compoundDetail(
        string $group,
        string $form,
        string $medicine,
        float $exactQuantity,
        float $stockQuantity,
        float $lineTotal,
        float $embalase,
    ): PenjualanTransactionDetailModel {
        return (new PenjualanTransactionDetailModel)->forceFill([
            'nama_obat' => $medicine,
            'satuan_jual' => 'tablet',
            'qty_jual' => $stockQuantity,
            'harga_jual' => 1000,
            'subtotal_gross' => $lineTotal,
            'total_line' => $lineTotal,
            'aturan_pakai' => '3 x sehari 1 dosis',
            'durasi_hari' => 4,
            'racikan_group' => $group,
            'bentuk_racikan' => $form,
            'jumlah_racikan' => 10,
            'jumlah_ambil_resep' => 10,
            'signa_1' => '3',
            'signa_2' => '1',
            'embalase_racikan' => $embalase,
            'dosis_komponen' => '30 mg',
            'kekuatan_obat' => '500 mg',
            'jumlah_resep' => $exactQuantity,
        ]);
    }
}
