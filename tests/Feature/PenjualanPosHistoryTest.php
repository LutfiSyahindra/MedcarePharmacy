<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PenjualanPosHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'CB-HISTORY',
            'name' => 'Cabang Riwayat',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
    }

    public function test_admin_must_choose_from_active_branches_when_opening_pos(): void
    {
        $this->user->assignRole(Role::findOrCreate('Admin', 'web'));
        $activeBranch = BranchModel::create([
            'code' => 'CB-ACTIVE',
            'name' => 'Cabang Aktif Pilihan',
            'is_active' => true,
        ]);
        $inactiveBranch = BranchModel::create([
            'code' => 'CB-INACTIVE',
            'name' => 'Cabang Nonaktif',
            'is_active' => false,
        ]);

        $this->actingAs($this->user)
            ->get(route('penjualan.pos'))
            ->assertOk()
            ->assertSee('posBranchModal', false)
            ->assertSee('posReceiptModal', false)
            ->assertSee('compoundPreviewStep', false)
            ->assertSee('Preview resep racikan')
            ->assertSee('cart-unit-select', false)
            ->assertSee('aria-label="Bayar resep racikan"', false)
            ->assertSee('pos-compound-preview-pay', false)
            ->assertSee('Etiket resep')
            ->assertDontSee('window.open', false)
            ->assertSee('Pilih cabang POS')
            ->assertSee($this->branch->name)
            ->assertSee($activeBranch->name)
            ->assertDontSee($inactiveBranch->name);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.products'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.products', ['branch_id' => $activeBranch->id]))
            ->assertOk();

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.products', ['branch_id' => $inactiveBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_non_admin_cannot_use_an_unassigned_pos_branch(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'CB-POS-FOREIGN',
            'name' => 'Cabang POS Asing',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.products', ['branch_id' => $foreignBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.products', ['branch_id' => $this->branch->id]))
            ->assertOk();
    }

    public function test_history_page_renders_the_new_workspace(): void
    {
        $this->actingAs($this->user)
            ->get(route('penjualan.pos.history'))
            ->assertOk()
            ->assertSee('Pusat aktivitas kasir')
            ->assertSee('Resep Non Racikan')
            ->assertSee('Resep Racikan')
            ->assertSee('history-summary-grid', false)
            ->assertSee('history-filter-panel', false)
            ->assertSee('transactionDrawer', false);
    }

    public function test_history_table_returns_scoped_rows_and_filtered_summary(): void
    {
        $paid = $this->createTransaction([
            'nomor_transaksi' => 'POS-PAID-001',
            'status' => 'completed',
            'payment_status' => 'paid',
            'grand_total' => 100000,
            'total_bayar' => 100000,
            'sisa_tagihan' => 0,
            'customer_name' => 'Pelanggan Tunai',
        ]);
        $credit = $this->createTransaction([
            'nomor_transaksi' => 'POS-CREDIT-001',
            'status' => 'completed',
            'payment_status' => 'credit',
            'grand_total' => 200000,
            'total_bayar' => 150000,
            'sisa_tagihan' => 50000,
            'customer_name' => 'Pelanggan Tagihan',
        ]);
        $this->createTransaction([
            'nomor_transaksi' => 'POS-DRAFT-001',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'grand_total' => 75000,
            'total_bayar' => 0,
            'sisa_tagihan' => 75000,
        ]);

        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $paid->id,
            'nama_obat' => 'Paracetamol',
            'qty_jual' => 2,
            'harga_jual' => 50000,
            'total_line' => 100000,
        ]);

        $foreignBranch = BranchModel::create([
            'code' => 'CB-FOREIGN',
            'name' => 'Cabang Lain',
            'is_active' => true,
        ]);
        $foreignTransaction = $this->createTransaction([
            'branch_id' => $foreignBranch->id,
            'nomor_transaksi' => 'POS-FOREIGN-001',
            'status' => 'completed',
            'payment_status' => 'paid',
            'grand_total' => 900000,
            'total_bayar' => 900000,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.table', [
                'draw' => 1,
                'start' => 0,
                'length' => 15,
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('summary.total', 3)
            ->assertJsonPath('summary.completed', 2)
            ->assertJsonPath('summary.draft', 1)
            ->assertJsonPath('summary.grand_total', 300000)
            ->assertJsonPath('summary.sisa_tagihan', 50000)
            ->assertJsonPath('summary.average_ticket', 150000);

        $this->assertSame(1, collect($response->json('data'))->firstWhere('id', $paid->id)['item_count']);
        $this->assertNull(collect($response->json('data'))->firstWhere('id', $foreignTransaction->id));

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.table', [
                'draw' => 2,
                'start' => 0,
                'length' => 15,
                'payment_status' => 'credit',
                'history_search' => 'Tagihan',
            ]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $credit->id)
            ->assertJsonPath('data.0.payment_label', 'Tagihan')
            ->assertJsonPath('summary.total', 1)
            ->assertJsonPath('summary.sisa_tagihan', 50000);
    }

    public function test_history_filters_are_validated(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.table', [
                'status' => 'unknown',
                'date_start' => '2026-07-20',
                'date_end' => '2026-07-19',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'date_end']);
    }

    public function test_transaction_detail_is_not_visible_outside_user_branches(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'CB-HIDDEN',
            'name' => 'Cabang Tersembunyi',
            'is_active' => true,
        ]);
        $transaction = $this->createTransaction([
            'branch_id' => $foreignBranch->id,
            'nomor_transaksi' => 'POS-HIDDEN-001',
        ]);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.show', $transaction->id))
            ->assertNotFound();
    }

    public function test_draft_detail_exposes_available_unit_conversions_for_cart_editing(): void
    {
        $tablet = SatuansModel::create([
            'kode' => 'TAB-POS',
            'nama' => 'Tablet',
            'is_active' => true,
        ]);
        $box = SatuansModel::create([
            'kode' => 'BOX-POS',
            'nama' => 'Box',
            'is_active' => true,
        ]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBAT-POS-UNIT',
            'nama_obat' => 'Obat Uji Konversi POS',
            'satuan_id' => $tablet->id,
            'is_active' => true,
        ]);
        KonversiSatuanModel::create([
            'obat_id' => $medicine->id,
            'satuan_id' => $box->id,
            'konversi' => 10,
            'is_default' => false,
        ]);

        $transaction = $this->createTransaction([
            'nomor_transaksi' => 'POS-DRAFT-UNIT-001',
        ]);
        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'obat_id' => $medicine->id,
            'satuan_id' => $tablet->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'satuan_jual' => $tablet->nama,
            'satuan_stok' => $tablet->nama,
            'konversi' => 1,
            'qty_jual' => 2,
            'qty_stok' => 2,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.show', $transaction->id))
            ->assertOk()
            ->assertJsonCount(2, 'details.0.units')
            ->assertJsonPath('details.0.units.0.satuan_id', $tablet->id)
            ->assertJsonPath('details.0.units.0.nama', 'Tablet')
            ->assertJsonPath('details.0.units.0.konversi', 1)
            ->assertJsonPath('details.0.units.1.satuan_id', $box->id)
            ->assertJsonPath('details.0.units.1.nama', 'Box')
            ->assertJsonPath('details.0.units.1.konversi', 10);
    }

    public function test_receipt_uses_the_transaction_branch_apotek_profile(): void
    {
        $this->branch->update([
            'name' => 'Nama Cabang Lama',
            'address' => 'Alamat cabang lama',
            'phone' => '0210000000',
            'email' => 'kontak-cabang-lama@example.test',
        ]);

        $transaction = $this->createTransaction([
            'nomor_transaksi' => 'POS-PROFILE-001',
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        ApotekProfile::create([
            'branch_id' => $this->branch->id,
            'name' => 'Apotek Profil Utama',
            'slogan' => 'Sehat dekat bersama kami',
            'logo_path' => 'apotek-logos/logo-profil.png',
            'phone' => '0217654321',
            'whatsapp' => '6281234567890',
            'email' => 'kasir@profil-apotek.test',
            'website' => 'https://profil-apotek.test',
            'instagram' => '@profil_apotek',
            'address' => 'Jl. Profil Apotek No. 10',
            'village' => 'Gambir',
            'district' => 'Gambir',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10110',
            'latitude' => -6.2,
            'longitude' => 106.8166667,
            'pharmacist_name' => 'apt. Siti Sehat, S.Farm.',
            'pharmacist_license_number' => 'SIPA-PROFILE-001',
            'pharmacy_license_number' => 'SIA-PROFILE-001',
            'tax_id' => '00.000.000.0-000.001',
            'receipt_footer' => 'Pesan khusus dari profil apotek.',
        ]);

        $foreignBranch = BranchModel::create([
            'code' => 'CB-RECEIPT-FOREIGN',
            'name' => 'Cabang Struk Lain',
            'is_active' => true,
        ]);
        ApotekProfile::create([
            'branch_id' => $foreignBranch->id,
            'name' => 'Apotek Profil Cabang Lain',
            'address' => 'Alamat cabang lain',
            'latitude' => -7.0,
            'longitude' => 107.0,
        ]);

        $this->actingAs($this->user)
            ->get(route('penjualan.pos.receipt', [
                'id' => $transaction->id,
                'embedded' => 1,
                'autoprint' => 1,
            ]))
            ->assertOk()
            ->assertSee('class="is-embedded"', false)
            ->assertDontSee('class="print-button"', false)
            ->assertSee('window.print()', false)
            ->assertSee('Apotek Profil Utama')
            ->assertSee('Sehat dekat bersama kami')
            ->assertSee('storage/apotek-logos/logo-profil.png', false)
            ->assertSee('Jl. Profil Apotek No. 10')
            ->assertDontSee('Kel. Gambir')
            ->assertDontSee('Kec. Gambir')
            ->assertDontSee('Jakarta Pusat, DKI Jakarta, 10110')
            ->assertSee('0217654321')
            ->assertSee('6281234567890')
            ->assertSee('kasir@profil-apotek.test')
            ->assertSee('https://profil-apotek.test')
            ->assertSee('@profil_apotek')
            ->assertSee('Pesan khusus dari profil apotek.')
            ->assertSee('apt. Siti Sehat, S.Farm.')
            ->assertSee('SIPA-PROFILE-001')
            ->assertSee('SIA-PROFILE-001')
            ->assertSee('00.000.000.0-000.001')
            ->assertDontSee('Apotek Profil Cabang Lain')
            ->assertDontSee('Nama Cabang Lama')
            ->assertDontSee('Alamat cabang lama')
            ->assertDontSee('0210000000')
            ->assertDontSee('kontak-cabang-lama@example.test');

        $this->actingAs($this->user)
            ->get(route('penjualan.pos.receipt', [
                'id' => $transaction->id,
                'embedded' => 1,
                'autoprint' => 0,
            ]))
            ->assertOk()
            ->assertDontSee('window.print()', false);
    }

    public function test_compound_prescription_metadata_is_returned_in_transaction_detail(): void
    {
        $transaction = $this->createTransaction([
            'nomor_transaksi' => 'POS-RACIKAN-001',
            'jenis_transaksi' => 'penjualan_racikan',
            'status' => 'completed',
            'payment_status' => 'paid',
            'customer_name' => 'Siti Aminah',
            'nomor_resep' => 'RSP-2026-001',
            'tanggal_resep' => '2026-07-19',
            'dokter_name' => 'dr. Andini',
            'asal_resep' => 'Klinik Medcare',
            'embalase' => 5000,
        ]);
        ApotekProfile::create([
            'branch_id' => $this->branch->id,
            'name' => 'Apotek Racikan Sehat',
            'logo_path' => 'apotek-logos/logo-racikan.png',
            'phone' => '0215550123',
            'whatsapp' => '6281212345678',
            'address' => 'Jl. Racikan Aman No. 8',
        ]);

        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'nama_obat' => 'Paracetamol 500 mg',
            'satuan_jual' => 'tablet',
            'qty_jual' => 1,
            'harga_jual' => 1000,
            'total_line' => 1000,
            'aturan_pakai' => '3 x sehari 1 bungkus',
            'waktu_konsumsi' => 'sesudah_makan',
            'durasi_hari' => 3,
            'racikan_group' => 'R/ 1',
            'bentuk_racikan' => 'Kapsul',
            'jumlah_racikan' => 10,
            'jumlah_ambil_resep' => 10,
            'signa_1' => '3',
            'signa_2' => '1',
            'embalase_racikan' => 5000,
            'dosis_komponen' => '250 mg',
            'kekuatan_obat' => '500 mg',
            'jumlah_resep' => 0.6,
            'keterangan' => 'Habiskan',
        ]);

        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'nama_obat' => 'Vitamin C 500 mg',
            'satuan_jual' => 'tablet',
            'qty_jual' => 2,
            'harga_jual' => 1500,
            'total_line' => 3000,
            'aturan_pakai' => '2 x sehari 1 bungkus',
            'durasi_hari' => 5,
            'racikan_group' => 'R/ 2',
            'bentuk_racikan' => 'Pulveres',
            'jumlah_racikan' => 10,
            'jumlah_ambil_resep' => 10,
            'signa_1' => '2',
            'signa_2' => '1',
            'embalase_racikan' => 3000,
            'dosis_komponen' => '60 mg',
            'kekuatan_obat' => '500 mg',
            'jumlah_resep' => 1.2,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.show', $transaction->id))
            ->assertOk()
            ->assertJsonPath('jenis_label', 'Resep Racikan')
            ->assertJsonPath('nomor_resep', 'RSP-2026-001')
            ->assertJsonPath('tanggal_resep', '2026-07-19')
            ->assertJsonPath('dokter_name', 'dr. Andini')
            ->assertJsonPath('asal_resep', 'Klinik Medcare')
            ->assertJsonPath('embalase', 5000)
            ->assertJsonPath('can_print_labels', true)
            ->assertJsonPath('labels_url', route('penjualan.pos.labels', $transaction->id))
            ->assertJsonPath('details.0.aturan_pakai', '3 x sehari 1 bungkus')
            ->assertJsonPath('details.0.waktu_konsumsi', 'sesudah_makan')
            ->assertJsonPath('details.0.durasi_hari', 3)
            ->assertJsonPath('details.0.racikan_group', 'R/ 1')
            ->assertJsonPath('details.0.bentuk_racikan', 'Kapsul')
            ->assertJsonPath('details.0.jumlah_racikan', 10)
            ->assertJsonPath('details.0.jumlah_ambil_resep', 10)
            ->assertJsonPath('details.0.signa_1', '3')
            ->assertJsonPath('details.0.signa_2', '1')
            ->assertJsonPath('details.0.embalase_racikan', 5000)
            ->assertJsonPath('details.0.dosis_komponen', '250 mg')
            ->assertJsonPath('details.0.kekuatan_obat', '500 mg')
            ->assertJsonPath('details.0.jumlah_resep', 0.6);

        $receipt = $this->actingAs($this->user)
            ->get(route('penjualan.pos.receipt', ['id' => $transaction->id, 'embedded' => 1]))
            ->assertOk()
            ->assertSee('Salinan Pasien')
            ->assertSee('Salinan Apotek')
            ->assertSee('Lembar Peracikan')
            ->assertSee('Rincian Per Racikan')
            ->assertSee('Racikan 1 · Kapsul')
            ->assertSee('Racikan 2 · Pulveres')
            ->assertSee('Kebutuhan Tepat')
            ->assertSee('0,60 tablet');

        $receiptHtml = $receipt->getContent();
        $patientCopyEnd = strpos($receiptHtml, '<main class="receipt receipt-pharmacy-copy">');
        $this->assertNotFalse($patientCopyEnd);
        $pharmacyHtml = substr($receiptHtml, $patientCopyEnd);
        $this->assertStringNotContainsString('Paracetamol 500 mg', substr($receiptHtml, 0, $patientCopyEnd));
        $this->assertStringNotContainsString('Vitamin C 500 mg', substr($receiptHtml, 0, $patientCopyEnd));
        $this->assertStringContainsString('Paracetamol 500 mg', $pharmacyHtml);
        $this->assertStringContainsString('Vitamin C 500 mg', $pharmacyHtml);
        $this->assertSame(2, substr_count($receiptHtml, 'class="pharmacy-compound-group"'));
        $this->assertTrue(
            strpos($pharmacyHtml, 'Racikan 1') < strpos($pharmacyHtml, 'Paracetamol 500 mg')
            && strpos($pharmacyHtml, 'Paracetamol 500 mg') < strpos($pharmacyHtml, 'Racikan 2')
            && strpos($pharmacyHtml, 'Racikan 2') < strpos($pharmacyHtml, 'Vitamin C 500 mg')
        );

        $labels = $this->actingAs($this->user)
            ->get(route('penjualan.pos.labels', [
                'id' => $transaction->id,
                'embedded' => 1,
                'autoprint' => 0,
            ]))
            ->assertOk()
            ->assertSee('Etiket Racikan')
            ->assertSee('Apotek Racikan Sehat')
            ->assertSee('storage/apotek-logos/logo-racikan.png', false)
            ->assertSee('Logo branch Apotek Racikan Sehat')
            ->assertSee('Siti Aminah')
            ->assertSee('Racikan 1')
            ->assertSee('Racikan 2')
            ->assertSee('Aturan pakai')
            ->assertSee('0215550123')
            ->assertDontSee('6281212345678')
            ->assertDontSee('Jl. Racikan Aman No. 8')
            ->assertDontSee('Paracetamol 500 mg')
            ->assertDontSee('Vitamin C 500 mg');

        $this->assertSame(2, substr_count($labels->getContent(), 'class="label-sheet"'));
        $this->assertStringContainsString('size: 80mm auto;', $labels->getContent());
    }

    public function test_completed_non_compound_prescriptions_have_one_label_per_medicine(): void
    {
        $transaction = $this->createTransaction([
            'nomor_transaksi' => 'POS-RESEP-001',
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'completed',
            'payment_status' => 'paid',
            'customer_name' => 'Budi Santoso',
            'nomor_resep' => 'RSP-NR-001',
            'dokter_name' => 'dr. Maya',
        ]);
        ApotekProfile::create([
            'branch_id' => $this->branch->id,
            'name' => 'Apotek Non Racikan',
            'phone' => '021998877',
            'whatsapp' => '6281299998888',
            'address' => 'Jl. Etiket Non Racikan No. 5',
        ]);

        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'nama_obat' => 'Amoxicillin 500 mg',
            'satuan_jual' => 'Kapsul',
            'qty_jual' => 10,
            'harga_jual' => 1000,
            'total_line' => 10000,
            'aturan_pakai' => '3 x sehari 1 kapsul',
            'waktu_konsumsi' => 'sesudah_makan',
            'durasi_hari' => 3,
            'keterangan' => 'Harus dihabiskan',
        ]);
        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'nama_obat' => 'Cetirizine 10 mg',
            'satuan_jual' => 'Tablet',
            'qty_jual' => 5,
            'harga_jual' => 500,
            'total_line' => 2500,
            'aturan_pakai' => '1 x sehari 1 tablet',
            'waktu_konsumsi' => 'sesudah_makan',
            'durasi_hari' => 5,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.show', $transaction->id))
            ->assertOk()
            ->assertJsonPath('can_print_labels', true)
            ->assertJsonPath('labels_url', route('penjualan.pos.labels', $transaction->id));

        $labels = $this->actingAs($this->user)
            ->get(route('penjualan.pos.labels', [
                'id' => $transaction->id,
                'embedded' => 1,
                'autoprint' => 0,
            ]))
            ->assertOk()
            ->assertSee('Etiket Non Racikan')
            ->assertSee('OBAT RESEP')
            ->assertSee('Budi Santoso')
            ->assertSee('Amoxicillin 500 mg')
            ->assertSee('10 kapsul')
            ->assertSee('3 x sehari 1 kapsul')
            ->assertSee('Cetirizine 10 mg')
            ->assertSee('5 tablet')
            ->assertSee('1 x sehari 1 tablet')
            ->assertSee('021998877')
            ->assertDontSee('6281299998888')
            ->assertDontSee('Jl. Etiket Non Racikan No. 5');

        $this->assertSame(2, substr_count($labels->getContent(), 'class="label-sheet"'));
    }

    public function test_labels_are_only_available_for_completed_prescriptions(): void
    {
        $regular = $this->createTransaction([
            'status' => 'completed',
            'jenis_transaksi' => 'penjualan_bebas',
        ]);
        $draftCompound = $this->createTransaction([
            'jenis_transaksi' => 'penjualan_racikan',
            'status' => 'draft',
        ]);

        $this->actingAs($this->user)
            ->get(route('penjualan.pos.labels', $regular->id))
            ->assertNotFound();

        $this->actingAs($this->user)
            ->get(route('penjualan.pos.labels', $draftCompound->id))
            ->assertNotFound();
    }

    public function test_completed_prescriptions_require_professional_clinical_data(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('penjualan.pos.complete'), [
            'jenis_transaksi' => 'penjualan_racikan',
            'details' => [[
                'obat_id' => 999999,
                'qty' => 1,
            ]],
            'payments' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'customer_name',
                'nomor_resep',
                'tanggal_resep',
                'dokter_name',
                'details.0.aturan_pakai',
                'details.0.durasi_hari',
                'details.0.racikan_group',
                'details.0.bentuk_racikan',
                'details.0.jumlah_racikan',
                'details.0.jumlah_ambil_resep',
                'details.0.signa_1',
                'details.0.signa_2',
                'details.0.dosis_komponen',
                'details.0.jumlah_resep',
            ]);
    }

    public function test_compound_prescription_quantity_is_not_limited_to_three(): void
    {
        foreach ([1, 2, 4, 25] as $compoundQuantity) {
            $response = $this->actingAs($this->user)->postJson(route('penjualan.pos.complete'), [
                'jenis_transaksi' => 'penjualan_racikan',
                'customer_name' => 'Pasien Racikan',
                'nomor_resep' => 'RSP-QTY-'.$compoundQuantity,
                'tanggal_resep' => now()->toDateString(),
                'dokter_name' => 'dr. Penguji',
                'details' => [[
                    'obat_id' => 999999,
                    'qty' => $compoundQuantity,
                    'aturan_pakai' => '1 x sehari 1 kapsul',
                    'durasi_hari' => $compoundQuantity,
                    'racikan_group' => 'R/ 1',
                    'bentuk_racikan' => 'Kapsul',
                    'jumlah_racikan' => $compoundQuantity,
                    'jumlah_ambil_resep' => $compoundQuantity,
                    'signa_1' => '1',
                    'signa_2' => '1',
                    'dosis_komponen' => '500 mg',
                    'kekuatan_obat' => '500 mg',
                    'jumlah_resep' => $compoundQuantity,
                ]],
                'payments' => [],
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['details.0.obat_id'])
                ->assertJsonMissingValidationErrors(['details.0.jumlah_racikan']);
        }
    }

    public function test_compound_prescription_embalase_cannot_be_negative(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('penjualan.pos.complete'), [
                'jenis_transaksi' => 'penjualan_racikan',
                'embalase' => -1000,
                'details' => [[
                    'obat_id' => 999999,
                    'qty' => 1,
                ]],
                'payments' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['embalase']);
    }

    private function createTransaction(array $attributes = []): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::create(array_merge([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'POS-'.fake()->unique()->numerify('######'),
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'customer_name' => 'Pelanggan Umum',
            'subtotal_gross' => 0,
            'subtotal_net' => 0,
            'grand_total' => 0,
            'total_bayar' => 0,
            'sisa_tagihan' => 0,
            'created_by' => $this->user->id,
        ], $attributes));
    }
}
