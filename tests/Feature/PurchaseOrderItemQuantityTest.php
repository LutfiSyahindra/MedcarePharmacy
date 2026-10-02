<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\User;
use App\Repositories\Menu\PembelianPenerimaan\PembelianRepository;
use App\Services\Menu\PembelianPenerimaan\PembelianService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

class PurchaseOrderItemQuantityTest extends TestCase
{
    public function test_purchase_order_table_data_contains_item_and_quantity_totals(): void
    {
        $purchaseOrder = new PembelianModel([
            'id' => 41,
            'no_po' => 'PO-ITEM-QTY-001',
            'branch_id' => 7,
            'tanggal_po' => '2026-10-01',
            'total_estimasi' => 5500,
            'status' => 'draft',
            'details_count' => 2,
            'details_sum_qty' => 5.5,
        ]);
        $purchaseOrder->setRelation('details', new EloquentCollection([
            new PembelianDetailModel(['qty' => 2]),
            new PembelianDetailModel(['qty' => 3.5]),
        ]));
        $purchaseOrder->setRelation('branch', new BranchModel(['name' => 'Cabang Uji']));
        $purchaseOrder->setRelation('distributor', new DistributorModel(['nama' => 'Distributor Uji']));
        $purchaseOrder->setRelation('createdBy', new User(['name' => 'User Uji']));
        $purchaseOrder->setRelation('approvedBy', null);

        $orders = new class([$purchaseOrder]) extends EloquentCollection
        {
            public function load($relations)
            {
                return $this;
            }

            public function loadCount($relations)
            {
                return $this;
            }

            public function loadSum($relations, $column)
            {
                return $this;
            }

            public function loadExists($relations)
            {
                return $this;
            }
        };
        $repository = new class($orders) extends PembelianRepository
        {
            public function __construct(private readonly EloquentCollection $orders) {}

            public function getPembelian(?array $branchIds = null, ?string $medicineSearch = null, ?int $medicineId = null)
            {
                return $this->orders;
            }
        };
        $row = collect((new PembelianService($repository))->getPembelianTable([7]))
            ->firstWhere('id', 41);

        $this->assertNotNull($row);
        $this->assertSame(2, $row['item_count']);
        $this->assertSame(5.5, $row['total_qty']);
    }

    public function test_purchase_order_table_renders_item_quantity_column_and_keeps_status_filter_aligned(): void
    {
        $view = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/pembelian.blade.php'
        ));
        $script = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php'
        ));

        $this->assertIsString($view);
        $this->assertIsString($script);
        $this->assertStringContainsString('<th>Item / Qty</th>', $view);
        $this->assertStringContainsString("data: 'item_count'", $script);
        $this->assertStringContainsString('row.total_qty', $script);
        $this->assertStringContainsString('PembelianTable.column(8)', $script);
    }
}
