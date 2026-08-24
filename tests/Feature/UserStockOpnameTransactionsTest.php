<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\Menu\Stok\StockOpnameLogModel;
use App\Models\Menu\Stok\StockOpnameModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStockOpnameTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_exposes_each_users_stock_opname_transactions(): void
    {
        $viewer = User::factory()->create();
        $participant = User::factory()->create();
        $otherUser = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'USR-SO',
            'name' => 'Cabang Riwayat User',
            'is_active' => true,
        ]);

        $createdOpname = StockOpnameModel::create([
            'branch_id' => $branch->id,
            'nomor' => 'SO-USR-001',
            'tanggal_opname' => now()->subDay()->toDateString(),
            'status' => StockOpnameModel::STATUS_DRAFT,
            'created_by' => $participant->id,
        ]);
        StockOpnameLogModel::create([
            'stock_opname_id' => $createdOpname->id,
            'action' => 'create',
            'to_status' => StockOpnameModel::STATUS_DRAFT,
            'note' => 'Draft dibuat oleh user.',
            'performed_by' => $participant->id,
            'performed_at' => now()->subDay(),
        ]);

        $loggedOpname = StockOpnameModel::create([
            'branch_id' => $branch->id,
            'nomor' => 'SO-USR-002',
            'tanggal_opname' => now()->toDateString(),
            'status' => StockOpnameModel::STATUS_COUNTING,
            'created_by' => $otherUser->id,
        ]);
        StockOpnameLogModel::create([
            'stock_opname_id' => $loggedOpname->id,
            'action' => 'update',
            'from_status' => StockOpnameModel::STATUS_COUNTING,
            'to_status' => StockOpnameModel::STATUS_COUNTING,
            'note' => 'Catatan diperbarui oleh user.',
            'performed_by' => $participant->id,
            'performed_at' => now(),
        ]);

        $this->actingAs($viewer);

        $historyResponse = $this->getJson(route('users.stockOpnames', $participant->id))
            ->assertOk()
            ->assertJsonPath('user.id', $participant->id)
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.counting', 1)
            ->assertJsonPath('transactions.0.nomor', 'SO-USR-002')
            ->assertJsonPath('transactions.0.roles.0', 'Pelaksana')
            ->assertJsonPath('transactions.0.activities.0.label', 'Memperbarui draft')
            ->assertJsonPath('transactions.1.nomor', 'SO-USR-001')
            ->assertJsonPath('transactions.1.roles.0', 'Pembuat');

        $this->assertCount(2, $historyResponse->json('transactions'));

        $tableResponse = $this->getJson(route('users.table'))->assertOk();
        $participantRow = collect($tableResponse->json('data'))->firstWhere('id', $participant->id);

        $this->assertNotNull($participantRow);
        $this->assertSame(2, $participantRow['stock_opname_transactions_count']);
    }

    public function test_stock_opname_history_returns_not_found_for_unknown_user(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('users.stockOpnames', 999999))
            ->assertNotFound();
    }
}
