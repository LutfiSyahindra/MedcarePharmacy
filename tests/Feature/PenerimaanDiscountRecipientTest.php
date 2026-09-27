<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
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
