<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderEstimateStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private DistributorModel $distributor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'PO-ESTIMATE', 'name' => 'Cabang Estimasi', 'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
        $this->distributor = DistributorModel::create([
            'kode' => 'DST-ESTIMATE', 'nama' => 'Distributor Estimasi', 'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    public function test_estimate_follows_each_status_across_pages_and_restores_all_statuses(): void
    {
        $this->createOrder('draft', 100.25);
        $this->createOrder('draft', 200.50);
        $this->createOrder('waiting_approval', 50);
        $this->createOrder('approved', 500.25);
        $this->createOrder('rejected', 75);
        $receiving = $this->createOrder('approved', 150);
        PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-ESTIMATE-001',
            'purchase_order_id' => $receiving->id,
            'distributor_id' => $this->distributor->id,
            'nomor_faktur' => 'INV-ESTIMATE-001',
            'tanggal_penerimaan' => '2026-10-03',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        foreach ([
            'draft' => 300.75,
            'waiting_approval' => 50,
            'approved' => 500.25,
            'dalam_penerimaan' => 150,
            'selesai' => 0,
            'rejected' => 75,
            '' => 1076,
        ] as $status => $expected) {
            $this->getJson(route('pembelian.table', $this->statusFilter($status) + ['start' => 0, 'length' => 1]))
                ->assertOk()
                ->assertJsonPath('summary.total_estimasi', $expected)
                ->assertJsonPath('summary.total', 6)
                ->assertJsonPath('summary.draft', 2)
                ->assertJsonPath('summary.approved', 1)
                ->assertJsonPath('summary.dalam_penerimaan', 1)
                ->assertJsonCount($expected === 0 ? 0 : 1, 'data');
        }

        $this->getJson(route('pembelian.table', $this->statusFilter('draft') + ['start' => 1, 'length' => 1]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonPath('summary.total_estimasi', 300.75)
            ->assertJsonCount(1, 'data');
    }

    public function test_empty_status_results_have_zero_estimate_and_other_branches_are_excluded(): void
    {
        $this->createOrder('draft', 100);
        $otherBranch = BranchModel::create([
            'code' => 'PO-ESTIMATE-OTHER', 'name' => 'Cabang Lain', 'is_active' => true,
        ]);
        $this->createOrder('approved', 900)->update(['branch_id' => $otherBranch->id]);

        $this->getJson(route('pembelian.table', $this->statusFilter('approved')))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 0)
            ->assertJsonPath('summary.total_estimasi', 0)
            ->assertJsonCount(0, 'data');

        $this->getJson(route('pembelian.table', $this->statusFilter('')))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('summary.total_estimasi', 100);
    }

    private function statusFilter(string $status): array
    {
        return ['columns' => [
            ['data' => 'no_po', 'name' => 'no_po', 'searchable' => 'true', 'search' => ['value' => '']],
            ['data' => 'status', 'name' => 'status', 'searchable' => 'true',
                'search' => ['value' => $status, 'regex' => 'false']],
        ]];
    }

    private function createOrder(string $status, float $total): PembelianModel
    {
        return PembelianModel::create([
            'no_po' => 'PO-ESTIMATE-'.(PembelianModel::count() + 1),
            'distributor_id' => $this->distributor->id,
            'branch_id' => $this->branch->id,
            'tanggal_po' => '2026-10-03',
            'status' => $status,
            'total_estimasi' => $total,
            'created_by' => $this->user->id,
        ]);
    }
}
