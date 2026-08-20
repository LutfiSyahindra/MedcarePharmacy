<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentLabelArchiveTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'CB-LABEL-DOC',
            'name' => 'Cabang Arsip Etiket',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
    }

    public function test_label_archive_is_available_from_the_document_menu(): void
    {
        $this->actingAs($this->user)
            ->get(route('dokumen.etiket.index'))
            ->assertOk()
            ->assertSee('Arsip Etiket Resep')
            ->assertSee('Etiket obat tersusun rapi dan siap dicetak ulang.')
            ->assertSee('Template Etiket')
            ->assertSee('Etiket Non Racikan')
            ->assertSee('Etiket Racikan')
            ->assertSee('Lihat Template')
            ->assertSee('Arsip Etiket')
            ->assertSee('const labelEndpoint', false)
            ->assertSee('<a href="'.route('dokumen.etiket.index').'"', false)
            ->assertSee('class="nav-link active">Etiket</a>', false)
            ->assertSee('.document-table tbody td { font-size: 13px; }', false);
    }

    public function test_empty_label_templates_render_for_both_prescription_types(): void
    {
        $this->actingAs($this->user)
            ->get(route('dokumen.etiket.template', [
                'type' => 'non-racikan',
                'branch_id' => $this->branch->id,
            ]))
            ->assertOk()
            ->assertSee('Template Etiket Kosong')
            ->assertSee('OBAT RESEP')
            ->assertSee('Nama obat')
            ->assertSee($this->branch->name)
            ->assertDontSee('window.setTimeout', false);

        $this->actingAs($this->user)
            ->get(route('dokumen.etiket.template', [
                'type' => 'racikan',
                'branch_id' => $this->branch->id,
                'print' => 1,
            ]))
            ->assertOk()
            ->assertSee('Template Etiket Kosong')
            ->assertSee('OBAT RACIKAN')
            ->assertSee('Nama / bentuk racikan')
            ->assertSee('window.setTimeout', false);
    }

    public function test_empty_label_template_rejects_an_inaccessible_branch(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'CB-TEMPLATE-FOREIGN',
            'name' => 'Cabang Template Asing',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->get(route('dokumen.etiket.template', [
                'type' => 'non-racikan',
                'branch_id' => $foreignBranch->id,
            ]))
            ->assertNotFound();
    }

    public function test_label_archive_only_returns_completed_prescriptions_from_accessible_branches(): void
    {
        $nonCompound = $this->createTransaction([
            'nomor_transaksi' => 'POS-LABEL-NON-001',
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'completed',
            'nomor_resep' => 'RX-NON-001',
            'customer_name' => 'Pasien Non Racikan',
            'dokter_name' => 'dr. Non Racikan',
        ]);
        $this->createDetail($nonCompound, 'Paracetamol', null);
        $this->createDetail($nonCompound, 'Cetirizine', null);

        $compound = $this->createTransaction([
            'nomor_transaksi' => 'POS-LABEL-MIX-001',
            'jenis_transaksi' => 'penjualan_racikan',
            'status' => 'completed',
            'nomor_resep' => 'RX-MIX-001',
            'customer_name' => 'Pasien Racikan',
            'dokter_name' => 'dr. Racikan',
        ]);
        $this->createDetail($compound, 'Obat Racik A', 'R/ 1');
        $this->createDetail($compound, 'Obat Racik B', 'R/ 1');
        $this->createDetail($compound, 'Obat Racik C', 'R/ 2');

        $draft = $this->createTransaction([
            'nomor_transaksi' => 'POS-LABEL-DRAFT',
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'draft',
        ]);
        $this->createDetail($draft, 'Obat Draft', null);

        $regular = $this->createTransaction([
            'nomor_transaksi' => 'POS-LABEL-REGULAR',
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
        ]);
        $this->createDetail($regular, 'Obat Bebas', null);

        $foreignBranch = BranchModel::create([
            'code' => 'CB-LABEL-FOREIGN',
            'name' => 'Cabang Etiket Lain',
            'is_active' => true,
        ]);
        $foreign = $this->createTransaction([
            'branch_id' => $foreignBranch->id,
            'nomor_transaksi' => 'POS-LABEL-FOREIGN',
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'completed',
        ]);
        $this->createDetail($foreign, 'Obat Cabang Lain', null);

        $response = $this->actingAs($this->user)->getJson(route('dokumen.etiket.table', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('summary.sets', 2)
            ->assertJsonPath('summary.labels', 4)
            ->assertJsonPath('summary.non_compound', 2)
            ->assertJsonPath('summary.compound', 2);

        $rows = collect($response->json('data'));
        $this->assertSame(2, $rows->firstWhere('id', $nonCompound->id)['label_count']);
        $this->assertSame('Etiket Non Racikan', $rows->firstWhere('id', $nonCompound->id)['type_label']);
        $this->assertSame(2, $rows->firstWhere('id', $compound->id)['label_count']);
        $this->assertSame('Etiket Racikan', $rows->firstWhere('id', $compound->id)['type_label']);
        $this->assertStringContainsString('/labels?autoprint=0', $rows->firstWhere('id', $compound->id)['preview_url']);
        $this->assertStringContainsString('/labels?autoprint=1', $rows->firstWhere('id', $compound->id)['print_url']);
        $this->assertNull($rows->firstWhere('id', $foreign->id));
    }

    public function test_label_archive_filters_search_type_and_branch_access(): void
    {
        $nonCompound = $this->createTransaction([
            'nomor_transaksi' => 'POS-FILTER-NON',
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'completed',
        ]);
        $this->createDetail($nonCompound, 'Obat Non Racikan', null);

        $compound = $this->createTransaction([
            'nomor_transaksi' => 'POS-FILTER-MIX',
            'jenis_transaksi' => 'penjualan_racikan',
            'status' => 'completed',
        ]);
        $this->createDetail($compound, 'Kapsul Racikan Khusus', 'R/ 1');

        $this->actingAs($this->user)
            ->getJson(route('dokumen.etiket.table', [
                'draw' => 2,
                'start' => 0,
                'length' => 10,
                'type' => 'penjualan_racikan',
                'etiket_search' => 'Kapsul Racikan Khusus',
            ]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $compound->id)
            ->assertJsonPath('summary.sets', 1)
            ->assertJsonPath('summary.compound', 1);

        $foreignBranch = BranchModel::create([
            'code' => 'CB-LABEL-NO-ACCESS',
            'name' => 'Cabang Tanpa Akses',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('dokumen.etiket.table', ['branch_id' => $foreignBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    }

    private function createTransaction(array $attributes = []): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::create(array_merge([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'POS-'.fake()->unique()->numerify('######'),
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'completed',
            'payment_status' => 'paid',
            'customer_name' => 'Pasien Etiket',
            'nomor_resep' => 'RX-'.fake()->unique()->numerify('######'),
            'dokter_name' => 'dr. Penguji',
            'subtotal_gross' => 0,
            'subtotal_net' => 0,
            'grand_total' => 0,
            'total_bayar' => 0,
            'sisa_tagihan' => 0,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => now(),
        ], $attributes));
    }

    private function createDetail(
        PenjualanTransactionModel $transaction,
        string $medicineName,
        ?string $compoundGroup,
    ): PenjualanTransactionDetailModel {
        return PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'nama_obat' => $medicineName,
            'qty_jual' => 1,
            'aturan_pakai' => '1 x sehari',
            'racikan_group' => $compoundGroup,
        ]);
    }
}
