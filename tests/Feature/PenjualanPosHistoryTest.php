<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
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
            'customer_name' => 'Siti Aminah',
            'nomor_resep' => 'RSP-2026-001',
            'tanggal_resep' => '2026-07-19',
            'dokter_name' => 'dr. Andini',
            'asal_resep' => 'Klinik Medcare',
            'embalase' => 5000,
        ]);

        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'nama_obat' => 'Paracetamol 500 mg',
            'qty_jual' => 10,
            'harga_jual' => 1000,
            'total_line' => 10000,
            'aturan_pakai' => '3 x sehari 1 bungkus',
            'waktu_konsumsi' => 'sesudah_makan',
            'durasi_hari' => 3,
            'racikan_group' => 'R/ 1',
            'dosis_komponen' => '250 mg',
            'keterangan' => 'Habiskan',
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
            ->assertJsonPath('details.0.aturan_pakai', '3 x sehari 1 bungkus')
            ->assertJsonPath('details.0.waktu_konsumsi', 'sesudah_makan')
            ->assertJsonPath('details.0.durasi_hari', 3)
            ->assertJsonPath('details.0.racikan_group', 'R/ 1')
            ->assertJsonPath('details.0.dosis_komponen', '250 mg');
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
                'details.0.racikan_group',
                'details.0.dosis_komponen',
            ]);
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
