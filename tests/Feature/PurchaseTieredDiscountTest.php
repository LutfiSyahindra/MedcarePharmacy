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
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PurchaseTieredDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_stores_three_tiered_discounts_and_receipt_inherits_them(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-DISC-PO',
            'name' => 'Cabang Diskon PO',
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);

        $unit = SatuansModel::create([
            'kode' => 'BOX-DISC',
            'nama' => 'BOX',
            'is_active' => true,
        ]);
        $distributor = DistributorModel::create([
            'kode' => 'DST-DISC',
            'nama' => 'Distributor Diskon',
            'is_active' => true,
        ]);
        $obat = MasterObatModel::create([
            'kode_obat' => 'OBT-DISC',
            'nama_obat' => 'Obat Diskon Bertingkat',
            'satuan_id' => $unit->id,
            'distributor_id' => $distributor->id,
            'is_active' => true,
        ]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $obat->id,
            'satuan_id' => $unit->id,
            'konversi' => 1,
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('pembelian.store'), [
                'no_po' => 'PO-DISC-001',
                'distributor_id' => $distributor->id,
                'tanggal' => '18-08-2026',
                'catatan' => 'Diskon berasal dari pembelian.',
                'total_estimasi' => 1,
                'obat_id' => [$obat->id],
                'qty' => [2],
                'harga_estimasi' => [100000],
                'diskon_1' => [10],
                'diskon_2' => [5],
                'diskon_3' => [2],
                'subtotal' => [1],
                'satuan_id' => [$conversion->id],
            ])
            ->assertOk();

        $purchaseOrder = PembelianModel::where('no_po', 'PO-DISC-001')->firstOrFail();
        $purchaseDetail = PembelianDetailModel::where('purchase_order_id', $purchaseOrder->id)->firstOrFail();

        $this->assertSame('10.00', $purchaseDetail->diskon_1);
        $this->assertSame('5.00', $purchaseDetail->diskon_2);
        $this->assertSame('2.00', $purchaseDetail->diskon_3);
        $this->assertSame('167580.00', $purchaseDetail->subtotal);
        $this->assertEquals(167580, (float) $purchaseOrder->total_estimasi);

        $purchaseOrder->update(['status' => 'approved']);

        $this->actingAs($user)
            ->postJson(route('penerimaan.store'), [
                'nomor_penerimaan' => 'PB-DISC-001',
                'purchase_order_id' => $purchaseOrder->id,
                'nomor_faktur' => 'INV-DISC-001',
                'tanggal_penerimaan' => '18-08-2026',
                'tanggal_faktur' => '18-08-2026',
                'purchase_order_detail_id' => [$purchaseDetail->id],
                'obat_id' => [$obat->id],
                'qty_diterima' => [2],
                'stok_batch_id' => [null],
                'no_batch' => ['BATCH-DISC-001'],
                'expired_date' => ['18-08-2027'],
                'harga_beli' => [100000],
                'diskon' => [99],
                'ppn' => [11],
            ])
            ->assertOk();

        $receipt = PenerimaanBarangModel::where('nomor_penerimaan', 'PB-DISC-001')->firstOrFail();
        $receiptDetail = PenerimaanBarangDetailModel::where('penerimaan_barang_id', $receipt->id)->firstOrFail();

        $this->assertSame('10.00', $receiptDetail->diskon_1);
        $this->assertSame('5.00', $receiptDetail->diskon_2);
        $this->assertSame('2.00', $receiptDetail->diskon_3);
        $this->assertSame('16.21', $receiptDetail->diskon);
        $this->assertEquals(32420, (float) $receiptDetail->nilai_diskon);
        $this->assertEquals(186013.80, (float) $receiptDetail->total);
        $this->assertEquals(186013.80, (float) $receipt->grand_total);
    }
}
