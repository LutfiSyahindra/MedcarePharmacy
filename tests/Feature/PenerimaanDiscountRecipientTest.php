<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Keuangan\FinanceTransactionModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PenerimaanDiscountRecipientTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_discount_posts_net_purchase_price_to_stock(): void
    {
        [$user, $receipt, $detail] = $this->receiptContext('PATIENT');

        $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('diskon_untuk');

        $this->actingAs($user)
            ->getJson(route('penerimaan.hargaJualPreview', [
                'id' => $receipt->id,
                'diskon_untuk' => 'pasien',
            ]))
            ->assertOk()
            ->assertJsonPath('header.diskon_untuk', 'pasien')
            ->assertJsonPath('details.0.harga_beli_stok', 8550)
            ->assertJsonPath('details.0.harga_beli_satuan_terkecil', 9490.5)
            ->assertJsonPath('details.0.harga_jual', 9490.5);

        $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id), [
                'diskon_untuk' => 'pasien',
            ])
            ->assertOk()
            ->assertJsonPath('diskon_untuk', 'pasien')
            ->assertJsonPath('selling_prices.0.harga_jual', 9490.5);

        $batch = StokBatchModel::where('no_batch', 'BATCH-PATIENT')->firstOrFail();

        $this->assertSame('pasien', $receipt->refresh()->diskon_untuk);
        $this->assertSame(8550.0, (float) $detail->refresh()->harga_beli_stok);
        $this->assertSame(8550.0, (float) $batch->harga_beli);
        $this->assertSame(0.0, (float) $batch->diskon);
        $this->assertSame(9490.5, (float) $batch->harga_jual);
        $this->assertSame(8550.0, (float) KartuStokModel::where('stok_batch_id', $batch->id)->firstOrFail()->harga_beli);

        $this->getJson(route('stok.batchTable', ['obat_id' => $detail->obat_id]))
            ->assertOk()
            ->assertJsonPath('data.0.diskon', 0)
            ->assertJsonPath('data.0.diskon_persen', 14.5)
            ->assertJsonPath('data.0.diskon_untuk', 'pasien')
            ->assertJsonPath('data.0.harga_jual_sebelum_diskon', 11100)
            ->assertJsonPath('data.0.harga_jual_sesudah_diskon', 9490.5)
            ->assertJsonPath('data.0.nilai_stok_jual', 94905);
    }

    public function test_pharmacy_discount_keeps_gross_purchase_price_in_stock(): void
    {
        [$user, $receipt, $detail] = $this->receiptContext('PHARMACY');

        $this->actingAs($user)
            ->getJson(route('penerimaan.hargaJualPreview', [
                'id' => $receipt->id,
                'diskon_untuk' => 'apotek',
            ]))
            ->assertOk()
            ->assertJsonPath('header.diskon_untuk', 'apotek')
            ->assertJsonPath('details.0.nilai_diskon_jual', 0)
            ->assertJsonPath('details.0.harga_beli_stok', 10000)
            ->assertJsonPath('details.0.harga_beli_satuan_terkecil', 11100)
            ->assertJsonPath('details.0.harga_jual', 11100);

        $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id), [
                'diskon_untuk' => 'apotek',
            ])
            ->assertOk()
            ->assertJsonPath('diskon_untuk', 'apotek')
            ->assertJsonPath('selling_prices.0.harga_jual', 11100);

        $batch = StokBatchModel::where('no_batch', 'BATCH-PHARMACY')->firstOrFail();

        $this->assertSame('apotek', $receipt->refresh()->diskon_untuk);
        $this->assertSame(10000.0, (float) $detail->refresh()->harga_beli_stok);
        $this->assertSame(10000.0, (float) $batch->harga_beli);
        $this->assertSame(0.0, (float) $batch->diskon);
        $this->assertSame(11100.0, (float) $batch->harga_jual);
        $this->assertSame(10000.0, (float) KartuStokModel::where('stok_batch_id', $batch->id)->firstOrFail()->harga_beli);

        $this->getJson(route('stok.batchTable', ['obat_id' => $detail->obat_id]))
            ->assertOk()
            ->assertJsonPath('data.0.diskon_persen', 14.5)
            ->assertJsonPath('data.0.diskon_untuk', 'apotek')
            ->assertJsonPath('data.0.harga_jual_sebelum_diskon', 11100)
            ->assertJsonPath('data.0.harga_jual_sesudah_diskon', 11100);
    }

    public function test_batch_discount_display_excludes_other_costs_and_unposted_receipts(): void
    {
        [$user, $receipt, $detail] = $this->receiptContext('DISPLAY');
        $receipt->update(['biaya_lain' => 1000]);

        $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id), ['diskon_untuk' => 'pasien'])
            ->assertOk();

        $batch = $detail->refresh()->stokBatch;
        $unpostedReceipt = $receipt->replicate()->fill([
            'nomor_penerimaan' => 'PB-DISC-UNPOSTED',
            'status' => 'draft',
            'diskon_untuk' => 'apotek',
        ]);
        $unpostedReceipt->save();
        $detail->replicate()->fill([
            'penerimaan_barang_id' => $unpostedReceipt->id,
            'diskon' => 50,
        ])->save();

        $this->getJson(route('stok.batchTable', ['obat_id' => $detail->obat_id]))
            ->assertOk()
            ->assertJsonPath('data.0.diskon_persen', 14.5)
            ->assertJsonPath('data.0.diskon_untuk', 'pasien')
            ->assertJsonPath('data.0.harga_jual_sebelum_diskon', 11200)
            ->assertJsonPath('data.0.harga_jual_sesudah_diskon', 9590.5);

        // Perubahan harga aktif tetap menjadi dasar tampilan harga sesudah diskon.
        $batch->update(['harga_jual' => 8650]);
        $this->getJson(route('stok.batchTable', ['obat_id' => $detail->obat_id]))
            ->assertOk()
            ->assertJsonPath('data.0.harga_jual_sebelum_diskon', 10100)
            ->assertJsonPath('data.0.harga_jual_sesudah_diskon', 8650);
    }

    public function test_post_refreshes_stale_cancellation_metadata_before_recording_stock(): void
    {
        [$user, $receipt, $detail] = $this->receiptContext('REPOST');
        $staleBatch = StokBatchModel::create([
            'branch_id' => $receipt->purchaseOrder->branch_id,
            'obat_id' => $detail->obat_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => 0,
            'harga_beli' => $detail->harga_beli_stok,
            'harga_jual' => 0,
            'diskon' => $detail->diskon,
            'ppn' => $detail->ppn,
            'created_by' => $user->id,
        ]);
        $detail->forceFill(['stok_batch_id' => $staleBatch->id])->save();
        $receipt->forceFill([
            'posted_by' => $user->id,
            'posted_at' => '2026-09-26 08:00:00',
            'cancelled_by' => $user->id,
            'cancelled_at' => '2026-09-26 09:00:00',
        ])->save();

        Carbon::setTestNow('2026-09-27 12:34:56');

        $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id), [
                'diskon_untuk' => 'pasien',
            ])
            ->assertOk();

        Carbon::setTestNow();

        $receipt->refresh();
        $movement = KartuStokModel::where('reference_type', PenerimaanBarangModel::class)
            ->where('reference_id', $receipt->id)
            ->firstOrFail();

        $this->assertSame('posted', $receipt->status);
        $this->assertSame('2026-09-27 12:34:56', $receipt->posted_at->format('Y-m-d H:i:s'));
        $this->assertNull($receipt->cancelled_by);
        $this->assertNull($receipt->cancelled_at);
        $this->assertSame('2026-09-27 12:34:56', $movement->tanggal_mutasi->format('Y-m-d H:i:s'));
        $this->assertNotSame($staleBatch->id, $movement->stok_batch_id);
        $this->assertSame($movement->stok_batch_id, $detail->refresh()->stok_batch_id);
    }

    public function test_initial_invoice_payment_is_recorded_in_finance_when_receipt_is_posted(): void
    {
        [$user, $receipt] = $this->receiptContext('PAID');
        $receipt->forceFill([
            'jumlah_dibayar' => 40000,
            'sisa_hutang' => 54905,
            'status_pembayaran' => 'sebagian',
        ])->save();

        $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id), [
                'diskon_untuk' => 'pasien',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method');

        $response = $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id), [
                'diskon_untuk' => 'pasien',
                'payment_method' => 'transfer',
                'payment_occurred_at' => '2026-09-27 10:30:00',
                'payment_reference_no' => 'TRF-RECEIPT-001',
            ])
            ->assertOk()
            ->assertJsonPath('diskon_untuk', 'pasien');

        $transaction = FinanceTransactionModel::where(
            'number',
            $response->json('finance_transaction_number')
        )->firstOrFail();

        $this->assertSame('supplier_payment', $transaction->type);
        $this->assertSame('supplier_payable', $transaction->source_type);
        $this->assertSame($receipt->id, $transaction->source_id);
        $this->assertSame('initial-payment', $transaction->source_key);
        $this->assertSame('transfer', $transaction->payment_method);
        $this->assertEquals(40000, (float) $transaction->amount);
        $this->assertSame('TRF-RECEIPT-001', $transaction->reference_no);
        $this->assertSame('posted', $receipt->fresh()->status);
        $this->assertEquals(40000, (float) $receipt->fresh()->jumlah_dibayar);
        $this->assertEquals(54905, (float) $receipt->fresh()->sisa_hutang);
    }

    /**
     * @return array{0: User, 1: PenerimaanBarangModel, 2: PenerimaanBarangDetailModel}
     */
    private function receiptContext(string $suffix): array
    {
        Notification::fake();

        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        $branch = BranchModel::create([
            'code' => 'CB-DISC-'.$suffix,
            'name' => 'Cabang Diskon '.$suffix,
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);

        $unit = SatuansModel::create([
            'kode' => 'PCS-'.$suffix,
            'nama' => 'PCS',
            'is_active' => true,
        ]);
        $distributor = DistributorModel::create([
            'kode' => 'DST-'.$suffix,
            'nama' => 'Distributor '.$suffix,
            'is_active' => true,
        ]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-'.$suffix,
            'nama_obat' => 'Obat '.$suffix,
            'satuan_id' => $unit->id,
            'distributor_id' => $distributor->id,
            'is_active' => true,
        ]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id,
            'satuan_id' => $unit->id,
            'konversi' => 10,
            'is_default' => true,
        ]);
        $purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-DISC-'.$suffix,
            'distributor_id' => $distributor->id,
            'branch_id' => $branch->id,
            'tanggal_po' => '2026-09-27',
            'total_estimasi' => 85500,
            'status' => 'approved',
            'created_by' => $user->id,
        ]);
        $purchaseDetail = PembelianDetailModel::create([
            'purchase_order_id' => $purchaseOrder->id,
            'obat_id' => $medicine->id,
            'qty' => 1,
            'harga_estimasi' => 100000,
            'diskon_1' => 10,
            'diskon_2' => 5,
            'diskon_3' => 0,
            'subtotal' => 85500,
            'satuan_konversi' => $conversion->id,
        ]);
        $receipt = PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-DISC-'.$suffix,
            'purchase_order_id' => $purchaseOrder->id,
            'distributor_id' => $distributor->id,
            'nomor_faktur' => 'INV-DISC-'.$suffix,
            'tanggal_penerimaan' => '2026-09-27',
            'tanggal_faktur' => '2026-09-27',
            'total_barang' => 1,
            'total_qty' => 1,
            'subtotal' => 100000,
            'total_diskon' => 14500,
            'total_ppn' => 9405,
            'grand_total' => 94905,
            'diskon' => 14500,
            'pajak' => 9405,
            'total_faktur' => 94905,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        $detail = PenerimaanBarangDetailModel::create([
            'penerimaan_barang_id' => $receipt->id,
            'purchase_order_detail_id' => $purchaseDetail->id,
            'obat_id' => $medicine->id,
            'qty_po' => 1,
            'qty_diterima' => 1,
            'qty_diterima_stok' => 10,
            'konversi_satuan' => 10,
            'satuan_beli' => 'BOX',
            'satuan_stok' => 'PCS',
            'no_batch' => 'BATCH-'.$suffix,
            'expired_date' => '2027-09-27',
            'harga_beli' => 100000,
            'harga_beli_stok' => 10000,
            'diskon_1' => 10,
            'diskon_2' => 5,
            'diskon_3' => 0,
            'diskon' => 14.5,
            'ppn' => 11,
            'subtotal' => 100000,
            'nilai_diskon' => 14500,
            'nilai_ppn' => 9405,
            'total' => 94905,
        ]);

        // Hindari constraint enum SQLite lama saat controller menyinkronkan status PO.
        $purchaseOrder->update(['status' => 'waiting_approval']);

        return [$user, $receipt, $detail];
    }
}
