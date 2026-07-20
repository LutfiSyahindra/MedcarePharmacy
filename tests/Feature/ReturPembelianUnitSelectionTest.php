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
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReturPembelianUnitSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_can_use_stock_unit_and_limits_are_checked_in_stock_quantity(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-RETUR-UNIT',
            'name' => 'Cabang Retur Unit',
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);

        $pcs = SatuansModel::create(['kode' => 'PCS-T', 'nama' => 'PCS', 'is_active' => true]);
        $box = SatuansModel::create(['kode' => 'BOX-T', 'nama' => 'BOX', 'is_active' => true]);
        $distributor = DistributorModel::create([
            'kode' => 'DST-RETUR',
            'nama' => 'Distributor Retur',
            'is_active' => true,
        ]);
        $obat = MasterObatModel::create([
            'kode_obat' => 'OBT-RETUR-UNIT',
            'nama_obat' => 'Obat Retur Unit',
            'satuan_id' => $pcs->id,
            'distributor_id' => $distributor->id,
            'is_active' => true,
        ]);
        $boxConversion = KonversiSatuanModel::create([
            'obat_id' => $obat->id,
            'satuan_id' => $box->id,
            'konversi' => 10,
            'is_default' => true,
        ]);
        $purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-RETUR-UNIT',
            'distributor_id' => $distributor->id,
            'branch_id' => $branch->id,
            'tanggal_po' => '2026-07-19',
            'status' => 'approved',
            'created_by' => $user->id,
        ]);
        $purchaseDetail = PembelianDetailModel::create([
            'purchase_order_id' => $purchaseOrder->id,
            'obat_id' => $obat->id,
            'qty' => 2,
            'harga_estimasi' => 100000,
            'subtotal' => 200000,
            'satuan_konversi' => $boxConversion->id,
        ]);
        $receipt = PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-RETUR-UNIT',
            'purchase_order_id' => $purchaseOrder->id,
            'distributor_id' => $distributor->id,
            'nomor_faktur' => 'INV-RETUR-UNIT',
            'tanggal_penerimaan' => '2026-07-19',
            'total_barang' => 1,
            'total_qty' => 2,
            'subtotal' => 200000,
            'grand_total' => 200000,
            'status' => 'posted',
            'created_by' => $user->id,
            'posted_by' => $user->id,
            'posted_at' => now(),
        ]);
        $batch = StokBatchModel::create([
            'branch_id' => $branch->id,
            'obat_id' => $obat->id,
            'no_batch' => 'BATCH-RETUR-UNIT',
            'expired_date' => '2027-07-19',
            'qty' => 20,
            'harga_beli' => 10000,
            'diskon' => 0,
            'ppn' => 0,
            'created_by' => $user->id,
        ]);
        $receiptDetail = PenerimaanBarangDetailModel::create([
            'penerimaan_barang_id' => $receipt->id,
            'purchase_order_detail_id' => $purchaseDetail->id,
            'obat_id' => $obat->id,
            'stok_batch_id' => $batch->id,
            'qty_po' => 2,
            'qty_diterima' => 2,
            'qty_diterima_stok' => 20,
            'konversi_satuan' => 10,
            'satuan_beli' => 'BOX',
            'satuan_stok' => 'PCS',
            'no_batch' => $batch->no_batch,
            'expired_date' => '2027-07-19',
            'harga_beli' => 100000,
            'harga_beli_stok' => 10000,
            'subtotal' => 200000,
            'total' => 200000,
        ]);

        $this->actingAs($user)
            ->postJson(route('returPembelian.store'), [
                'nomor_retur' => 'RPB-UNIT-001',
                'penerimaan_barang_id' => $receipt->id,
                'tanggal_retur' => '19-07-2026',
                'penerimaan_barang_detail_id' => [$receiptDetail->id],
                'qty_retur' => [5],
                'satuan_retur_id' => [$pcs->id],
                'alasan_item' => ['Kemasan rusak'],
            ])
            ->assertOk();

        $this->assertDatabaseHas('retur_pembelian_detail', [
            'penerimaan_barang_detail_id' => $receiptDetail->id,
            'satuan_retur_id' => $pcs->id,
            'satuan_beli' => 'PCS',
            'qty_retur' => 5,
            'qty_retur_stok' => 5,
            'konversi_satuan' => 1,
            'harga_beli' => 10000,
            'subtotal' => 50000,
        ]);

        $this->actingAs($user)
            ->getJson(route('returPembelian.receiptDetail', $receipt->id))
            ->assertOk()
            ->assertJsonPath('details.0.returned_qty_stok', 5)
            ->assertJsonPath('details.0.returnable_qty_stok', 15)
            ->assertJsonPath('details.0.returnable_qty', 1.5)
            ->assertJsonFragment([
                'satuan_id' => $pcs->id,
                'nama' => 'PCS',
                'konversi' => 1,
            ])
            ->assertJsonFragment([
                'satuan_id' => $box->id,
                'nama' => 'BOX',
                'konversi' => 10,
            ]);

        $this->actingAs($user)
            ->postJson(route('returPembelian.store'), [
                'nomor_retur' => 'RPB-UNIT-002',
                'penerimaan_barang_id' => $receipt->id,
                'tanggal_retur' => '19-07-2026',
                'penerimaan_barang_detail_id' => [$receiptDetail->id],
                'qty_retur' => [2],
                'satuan_retur_id' => [$box->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('qty_retur.0');
    }
}
