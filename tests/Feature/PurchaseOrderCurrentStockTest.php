<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderCurrentStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_medicine_options_include_current_stock_for_the_purchase_order_branch_only(): void
    {
        $branch = BranchModel::create([
            'code' => 'PO-STOCK',
            'name' => 'Cabang PO',
            'is_active' => true,
        ]);
        $otherBranch = BranchModel::create([
            'code' => 'PO-OTHER',
            'name' => 'Cabang Lain',
            'is_active' => true,
        ]);
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $user->branches()->attach($branch->id);
        $unit = SatuansModel::create([
            'kode' => 'TAB-PO-STOCK',
            'nama' => 'Tablet',
            'is_active' => true,
        ]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-PO-STOCK',
            'nama_obat' => 'Obat Stok PO',
            'satuan_id' => $unit->id,
            'is_active' => true,
        ]);

        StokBatchModel::create([
            'branch_id' => $branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'PO-BATCH-1',
            'expired_date' => today()->addYear(),
            'qty' => 12.5,
            'harga_beli' => 1000,
            'created_by' => $user->id,
        ]);
        StokBatchModel::create([
            'branch_id' => $branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'PO-BATCH-2',
            'expired_date' => today()->addMonths(6),
            'qty' => 7.5,
            'harga_beli' => 1000,
            'created_by' => $user->id,
        ]);
        StokBatchModel::create([
            'branch_id' => $otherBranch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'OTHER-BATCH',
            'expired_date' => today()->addYear(),
            'qty' => 99,
            'harga_beli' => 1000,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson(route('pembelian.getObat'));

        $response->assertOk()
            ->assertJsonPath('0.id', $medicine->id)
            ->assertJsonPath('0.stok_saat_ini', 20)
            ->assertJsonPath('0.satuan.nama', 'Tablet');
    }
}
