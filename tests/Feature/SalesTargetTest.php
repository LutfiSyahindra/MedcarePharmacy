<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\Menu\Analisis\OmzetTargetModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SalesTargetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-17 09:30:00');

        $this->user = User::factory()->create(['name' => 'Supervisor Penjualan']);
        $this->branch = BranchModel::create([
            'code' => 'TGT-01',
            'name' => 'Cabang Target',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_page_requires_authentication_and_renders_monthly_workspace(): void
    {
        $this->get(route('penjualan.targets.index'))->assertRedirect(route('login'));

        $this->actingAs($this->user)
            ->get(route('penjualan.targets.index'))
            ->assertOk()
            ->assertSee('Target &amp; Pencapaian Penjualan', false)
            ->assertSee('Performa 12 bulan')
            ->assertSee('Cabang Target');
    }

    public function test_target_is_upserted_per_branch_and_calendar_month(): void
    {
        $this->actingAs($this->user)->postJson(route('penjualan.targets.store'), [
            'branch_id' => $this->branch->id,
            'period' => '2026-01',
            'target_amount' => 200000,
        ])->assertOk()
            ->assertJsonPath('target.period', '2026-01')
            ->assertJsonPath('target.amount', 200000);

        $this->postJson(route('penjualan.targets.store'), [
            'branch_id' => $this->branch->id,
            'period' => '2026-01',
            'target_amount' => 250000,
        ])->assertOk()->assertJsonPath('target.amount', 250000);

        $this->assertDatabaseCount('omzet_targets', 1);
        $this->assertDatabaseHas('omzet_targets', [
            'branch_id' => $this->branch->id,
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'target_amount' => 250000,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_dashboard_calculates_monthly_net_realization_and_ignores_inactive_documents(): void
    {
        $this->actingAs($this->user)->postJson(route('penjualan.targets.store'), [
            'branch_id' => $this->branch->id,
            'period' => '2026-01',
            'target_amount' => 200000,
        ])->assertOk();

        $sale = $this->sale('TGT-SALE-001', '2026-01-10 10:00:00', 'completed', 150000);
        $this->sale('TGT-SALE-CANCELLED', '2026-01-11 10:00:00', 'cancelled', 999000);
        $this->salesReturn($sale, 'TGT-RET-POSTED', 'posted', 20000);
        $this->salesReturn($sale, 'TGT-RET-DRAFT', 'draft', 10000);

        $this->getJson(route('penjualan.targets.data', [
            'year' => 2026,
            'branch_id' => $this->branch->id,
        ]))->assertOk()
            ->assertJsonPath('dashboard.summary.target', 200000)
            ->assertJsonPath('dashboard.summary.realization', 130000)
            ->assertJsonPath('dashboard.summary.achievement_percent', 65)
            ->assertJsonPath('dashboard.summary.remaining', 70000)
            ->assertJsonPath('dashboard.summary.configured_months', 1)
            ->assertJsonPath('dashboard.rows.0.period', '2026-01')
            ->assertJsonPath('dashboard.rows.0.gross_sales', 150000)
            ->assertJsonPath('dashboard.rows.0.returns', 20000)
            ->assertJsonPath('dashboard.rows.0.realization', 130000)
            ->assertJsonPath('dashboard.rows.0.transactions', 1)
            ->assertJsonPath('dashboard.rows.0.status', 'Perlu Perhatian')
            ->assertJsonPath('dashboard.branches.0.configured_months', 1);
    }

    public function test_target_access_is_limited_to_user_branches_and_owned_target_can_be_deleted(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'TGT-X',
            'name' => 'Cabang Lain',
            'is_active' => true,
        ]);
        $foreignTarget = OmzetTargetModel::create([
            'branch_id' => $foreignBranch->id,
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'target_amount' => 500000,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.targets.data', ['year' => 2026, 'branch_id' => $foreignBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');

        $this->postJson(route('penjualan.targets.store'), [
            'branch_id' => $foreignBranch->id,
            'period' => '2026-02',
            'target_amount' => 100000,
        ])->assertUnprocessable()->assertJsonValidationErrors('branch_id');

        $this->deleteJson(route('penjualan.targets.destroy', $foreignTarget))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');

        $ownedTarget = OmzetTargetModel::create([
            'branch_id' => $this->branch->id,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'target_amount' => 100000,
        ]);
        $this->deleteJson(route('penjualan.targets.destroy', $ownedTarget))
            ->assertOk()
            ->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('omzet_targets', ['id' => $ownedTarget->id]);
    }

    private function sale(string $number, string $date, string $status, float $amount): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => $number,
            'tanggal_transaksi' => $date,
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => $status,
            'payment_status' => $status === 'completed' ? 'paid' : 'void',
            'grand_total' => $amount,
            'created_by' => $this->user->id,
            'completed_by' => $status === 'completed' ? $this->user->id : null,
            'completed_at' => $status === 'completed' ? $date : null,
        ]);
    }

    private function salesReturn(
        PenjualanTransactionModel $sale,
        string $number,
        string $status,
        float $amount,
    ): ReturPenjualanModel {
        return ReturPenjualanModel::create([
            'branch_id' => $this->branch->id,
            'penjualan_transaction_id' => $sale->id,
            'nomor_retur' => $number,
            'tanggal_retur' => '2026-01-15',
            'status' => $status,
            'total_item' => 1,
            'total_qty' => 1,
            'grand_total' => $amount,
            'created_by' => $this->user->id,
            'posted_at' => $status === 'posted' ? '2026-01-15 11:00:00' : null,
        ]);
    }
}
