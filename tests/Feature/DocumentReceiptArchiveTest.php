<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentReceiptArchiveTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'CB-NOTA-DOC',
            'name' => 'Cabang Arsip Nota',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
    }

    public function test_receipt_archive_and_all_transaction_templates_are_available_from_document_menu(): void
    {
        $response = $this->actingAs($this->user)->get(route('dokumen.nota.index'));

        $response->assertOk()
            ->assertSee('Arsip Nota Penjualan')
            ->assertSee('Arsip Nota')
            ->assertSee('Template Nota per Jenis Transaksi')
            ->assertSee('Penjualan Bebas')
            ->assertSee('Resep Non Racikan')
            ->assertSee('Resep Racikan')
            ->assertSee('Penjualan Kredit')
            ->assertSee('Penjualan Instansi')
            ->assertSee('const receiptEndpoint', false)
            ->assertSee('<a href="'.route('dokumen.nota.index').'"', false)
            ->assertSee('class="nav-link active">Nota</a>', false);
    }

    public function test_receipt_archive_only_returns_completed_transactions_from_accessible_branches_and_filters_type(): void
    {
        $freeSale = $this->createTransaction([
            'nomor_transaksi' => 'POS-NOTA-BEBAS-001',
            'jenis_transaksi' => 'penjualan_bebas',
            'grand_total' => 50000,
            'total_bayar' => 50000,
        ]);
        $this->createDetail($freeSale, 'Vitamin Bebas');

        $compound = $this->createTransaction([
            'nomor_transaksi' => 'POS-NOTA-RACIKAN-001',
            'jenis_transaksi' => 'penjualan_racikan',
            'customer_name' => 'Pasien Racikan Nota',
            'nomor_resep' => 'RX-NOTA-RACIKAN',
            'grand_total' => 75000,
            'total_bayar' => 75000,
        ]);
        $this->createDetail($compound, 'Komponen Racikan Khusus');

        $draft = $this->createTransaction([
            'nomor_transaksi' => 'POS-NOTA-DRAFT',
            'jenis_transaksi' => 'penjualan_resep',
            'status' => 'draft',
        ]);
        $this->createDetail($draft, 'Obat Draft');

        $foreignBranch = BranchModel::create([
            'code' => 'CB-NOTA-FOREIGN',
            'name' => 'Cabang Nota Asing',
            'is_active' => true,
        ]);
        $foreign = $this->createTransaction([
            'branch_id' => $foreignBranch->id,
            'nomor_transaksi' => 'POS-NOTA-FOREIGN',
            'jenis_transaksi' => 'penjualan_racikan',
        ]);
        $this->createDetail($foreign, 'Obat Cabang Lain');

        $response = $this->actingAs($this->user)->getJson(route('dokumen.nota.table', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.types.penjualan_bebas', 1)
            ->assertJsonPath('summary.types.penjualan_racikan', 1)
            ->assertJsonPath('summary.grand_total', 125000);

        $rows = collect($response->json('data'));
        $this->assertStringContainsString('/receipt?autoprint=0', $rows->firstWhere('id', $compound->id)['preview_url']);
        $this->assertStringContainsString('/receipt?autoprint=1', $rows->firstWhere('id', $compound->id)['print_url']);
        $this->assertNull($rows->firstWhere('id', $foreign->id));

        $this->actingAs($this->user)
            ->getJson(route('dokumen.nota.table', [
                'draw' => 2,
                'start' => 0,
                'length' => 10,
                'type' => 'penjualan_racikan',
                'nota_search' => 'Komponen Racikan Khusus',
            ]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $compound->id)
            ->assertJsonPath('data.0.type_label', 'Resep Racikan');
    }

    public function test_receipt_templates_render_fields_for_each_transaction_type(): void
    {
        $expectations = [
            'penjualan_bebas' => ['Template Nota', 'Penjualan Bebas', '[Nama produk / obat]'],
            'penjualan_resep' => ['Resep Non Racikan', '[Nomor resep]', '[Aturan pakai]'],
            'penjualan_racikan' => ['Resep Racikan', 'Salinan Pasien', 'Lembar Peracikan'],
            'penjualan_kredit' => ['Penjualan Kredit', 'Sisa Tagihan', '[Jatuh tempo / catatan kredit]'],
            'penjualan_instansi' => ['Nota Penjualan Instansi', '[Nama instansi / pelanggan]', '[Nama instansi]', '[Nomor referensi instansi]'],
        ];

        foreach ($expectations as $type => $texts) {
            $response = $this->actingAs($this->user)->get(route('dokumen.nota.template', [
                'type' => $type,
                'branch_id' => $this->branch->id,
            ]));

            $response->assertOk();
            foreach ($texts as $text) {
                $response->assertSee($text);
            }
        }
    }

    public function test_receipt_archive_rejects_inaccessible_branch(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'CB-NOTA-NO-ACCESS',
            'name' => 'Cabang Tanpa Akses Nota',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('dokumen.nota.table', ['branch_id' => $foreignBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);

        $this->actingAs($this->user)
            ->get(route('dokumen.nota.template', [
                'type' => 'penjualan_bebas',
                'branch_id' => $foreignBranch->id,
            ]))
            ->assertNotFound();
    }

    private function createTransaction(array $attributes = []): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::create(array_merge([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'POS-'.fake()->unique()->numerify('######'),
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
            'payment_status' => 'paid',
            'customer_name' => 'Pelanggan Nota',
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

    private function createDetail(PenjualanTransactionModel $transaction, string $medicine): PenjualanTransactionDetailModel
    {
        return PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'nama_obat' => $medicine,
            'satuan_jual' => 'Tablet',
            'satuan_stok' => 'Tablet',
            'qty_jual' => 1,
            'qty_stok' => 1,
            'harga_jual' => 10000,
            'subtotal_gross' => 10000,
            'subtotal_net' => 10000,
            'total_line' => 10000,
        ]);
    }
}
