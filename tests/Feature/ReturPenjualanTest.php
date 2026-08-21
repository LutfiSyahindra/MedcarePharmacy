<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Penjualan\PenjualanTransactionBatchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturPenjualanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private PenjualanTransactionModel $transaction;

    private PenjualanTransactionDetailModel $detail;

    private StokBatchModel $firstBatch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'CB-RPJ',
            'name' => 'Cabang Retur Penjualan',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);

        $unit = SatuansModel::create([
            'kode' => 'PCS-RPJ',
            'nama' => 'PCS',
            'is_active' => true,
        ]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBAT-RPJ-001',
            'nama_obat' => 'Obat Uji Retur Penjualan',
            'satuan_id' => $unit->id,
            'is_active' => true,
        ]);
        $this->firstBatch = StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'RPJ-BATCH-01',
            'expired_date' => now()->addYear()->toDateString(),
            'qty' => 5,
            'harga_beli' => 6000,
            'harga_jual' => 10000,
            'diskon' => 0,
            'ppn' => 0,
            'created_by' => $this->user->id,
        ]);
        $secondBatch = StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'RPJ-BATCH-02',
            'expired_date' => now()->addYears(2)->toDateString(),
            'qty' => 8,
            'harga_beli' => 6500,
            'harga_jual' => 10000,
            'diskon' => 0,
            'ppn' => 0,
            'created_by' => $this->user->id,
        ]);

        $this->transaction = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => 'PJ-RPJ-001',
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
            'payment_status' => 'paid',
            'customer_name' => 'Pelanggan Retur',
            'customer_phone' => '08123456789',
            'subtotal_gross' => 100000,
            'diskon_item_total' => 10000,
            'diskon_transaksi_percent' => 10,
            'diskon_transaksi_nominal' => 9000,
            'subtotal_net' => 81000,
            'embalase' => 5000,
            'pajak_percent' => 10,
            'pajak_total' => 8100,
            'grand_total' => 94100,
            'total_bayar' => 94100,
            'sisa_tagihan' => 0,
            'completed_at' => now(),
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
        ]);
        $this->detail = PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $this->transaction->id,
            'obat_id' => $medicine->id,
            'satuan_id' => $unit->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'satuan_jual' => 'PCS',
            'satuan_stok' => 'PCS',
            'konversi' => 1,
            'qty_jual' => 10,
            'qty_stok' => 10,
            'harga_jual' => 10000,
            'subtotal_gross' => 100000,
            'diskon_percent' => 10,
            'diskon_nominal' => 10000,
            'subtotal_net' => 90000,
            'total_line' => 90000,
        ]);

        PenjualanTransactionBatchModel::create([
            'penjualan_transaction_detail_id' => $this->detail->id,
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'stok_batch_id' => $this->firstBatch->id,
            'no_batch' => $this->firstBatch->no_batch,
            'expired_date' => $this->firstBatch->expired_date,
            'qty_stok' => 6,
            'harga_beli' => 6000,
            'harga_jual' => 10000,
            'subtotal_gross' => 60000,
        ]);
        PenjualanTransactionBatchModel::create([
            'penjualan_transaction_detail_id' => $this->detail->id,
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'stok_batch_id' => $secondBatch->id,
            'no_batch' => $secondBatch->no_batch,
            'expired_date' => $secondBatch->expired_date,
            'qty_stok' => 4,
            'harga_beli' => 6500,
            'harga_jual' => 10000,
            'subtotal_gross' => 40000,
        ]);
    }

    public function test_sales_return_page_and_transaction_options_are_branch_scoped(): void
    {
        $this->actingAs($this->user)
            ->get(route('returPenjualan.index'))
            ->assertOk()
            ->assertSee('Retur Penjualan')
            ->assertSee('salesReturnModal', false);

        $this->actingAs($this->user)
            ->getJson(route('returPenjualan.transactions'))
            ->assertOk()
            ->assertJsonPath('0.id', $this->transaction->id)
            ->assertJsonPath('0.returnable_items', 1);

        $foreignBranch = BranchModel::create([
            'code' => 'CB-RPJ-FOREIGN',
            'name' => 'Cabang Retur Asing',
            'is_active' => true,
        ]);
        $foreignTransaction = PenjualanTransactionModel::create([
            'branch_id' => $foreignBranch->id,
            'nomor_transaksi' => 'PJ-RPJ-FOREIGN',
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
            'payment_status' => 'paid',
            'grand_total' => 1000,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('returPenjualan.transaction', $foreignTransaction->id))
            ->assertNotFound();
    }

    public function test_draft_post_and_cancel_restore_the_exact_original_batch_and_refund_value(): void
    {
        $createResponse = $this->actingAs($this->user)
            ->postJson(route('returPenjualan.store'), $this->returnPayload(5))
            ->assertOk()
            ->assertJsonPath('return.status', 'draft')
            ->assertJsonPath('return.subtotal_gross', 50000)
            ->assertJsonPath('return.diskon_item_total', 5000)
            ->assertJsonPath('return.diskon_transaksi_total', 4500)
            ->assertJsonPath('return.pajak_total', 4050)
            ->assertJsonPath('return.grand_total', 44550);

        $returnId = (int) $createResponse->json('return.id');
        $this->assertSame(5.0, (float) $this->firstBatch->fresh()->qty);
        $this->assertDatabaseHas('retur_penjualan_batches', [
            'retur_penjualan_detail_id' => $createResponse->json('return.details.0.id'),
            'stok_batch_id' => $this->firstBatch->id,
            'qty_stok' => 5,
            'kartu_stok_id' => null,
        ]);

        $this->actingAs($this->user)
            ->putJson(route('returPenjualan.post', $returnId))
            ->assertOk()
            ->assertJsonPath('return.status', 'posted');

        $this->assertSame(10.0, (float) $this->firstBatch->fresh()->qty);
        $this->assertDatabaseHas('kartu_stok', [
            'stok_batch_id' => $this->firstBatch->id,
            'jenis_mutasi' => 'retur_penjualan',
            'qty_masuk' => 5,
            'nomor_referensi' => $createResponse->json('return.nomor_retur'),
        ]);

        $this->actingAs($this->user)
            ->postJson(route('returPenjualan.store'), $this->returnPayload(6))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('details');

        $this->actingAs($this->user)
            ->putJson(route('returPenjualan.cancel', $returnId), ['reason' => 'Pelanggan membatalkan proses retur.'])
            ->assertOk()
            ->assertJsonPath('return.status', 'cancelled');

        $this->assertSame(5.0, (float) $this->firstBatch->fresh()->qty);
        $this->assertDatabaseHas('kartu_stok', [
            'stok_batch_id' => $this->firstBatch->id,
            'jenis_mutasi' => 'pembatalan_retur_penjualan',
            'qty_keluar' => 5,
        ]);
    }

    public function test_active_sales_return_prevents_original_transaction_cancellation(): void
    {
        $return = $this->actingAs($this->user)
            ->postJson(route('returPenjualan.store'), $this->returnPayload(2))
            ->assertOk()
            ->json('return');

        $this->actingAs($this->user)
            ->putJson(route('penjualan.pos.cancel', $this->transaction->id), ['reason' => 'Salah transaksi'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('penjualan_transactions', [
            'id' => $this->transaction->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('retur_penjualan', [
            'id' => $return['id'],
            'status' => 'draft',
        ]);
    }

    public function test_cancelled_return_releases_quantity_for_a_new_return(): void
    {
        $firstReturn = $this->actingAs($this->user)
            ->postJson(route('returPenjualan.store'), $this->returnPayload(10))
            ->assertOk()
            ->json('return');

        $this->actingAs($this->user)
            ->putJson(route('returPenjualan.cancel', $firstReturn['id']), ['reason' => 'Draft tidak jadi diproses'])
            ->assertOk();

        $this->actingAs($this->user)
            ->postJson(route('returPenjualan.store'), $this->returnPayload(10))
            ->assertOk()
            ->assertJsonPath('return.grand_total', 89100);
    }

    private function returnPayload(float $qty): array
    {
        return [
            'penjualan_transaction_id' => $this->transaction->id,
            'tanggal_retur' => now()->toDateString(),
            'refund_method' => 'tunai',
            'refund_reference' => 'REF-RPJ-001',
            'alasan' => 'Barang dikembalikan pelanggan',
            'details' => [[
                'penjualan_transaction_detail_id' => $this->detail->id,
                'qty' => $qty,
                'alasan_item' => 'Tidak jadi digunakan',
            ]],
        ];
    }
}
