<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\Menu\Penjualan\CashierShiftModel;
use App\Models\Menu\Penjualan\PenjualanPaymentModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CashierShiftTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 8, 27, 3, 0, 0, 'UTC'));
        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'CB-SHIFT',
            'name' => 'Cabang Shift',
            'is_active' => true,
            'operational_timezone' => 'Asia/Jakarta',
        ]);
        ApotekProfile::create([
            'branch_id' => $this->branch->id,
            'name' => 'Apotek Shift',
            'address' => 'Jl. Shift No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'operational_hours' => $this->operationalHours(),
        ]);
        $this->user->branches()->attach($this->branch->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_pos_is_blocked_outside_profile_apotek_operational_hours(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 26, 23, 0, 0, 'UTC'));

        $this->actingAs($this->user)
            ->get(route('penjualan.pos'))
            ->assertRedirect(route('penjualan.pos.shifts'))
            ->assertSessionHas('cashier_closed');

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.products', ['branch_id' => $this->branch->id]))
            ->assertStatus(423)
            ->assertJsonPath('operational.is_open', false);
    }

    public function test_pos_requires_an_open_cashier_shift(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.products', ['branch_id' => $this->branch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cashier_shift']);

        $this->actingAs($this->user)
            ->postJson(route('penjualan.pos.shifts.open'), [
                'branch_id' => $this->branch->id,
                'opening_amount' => 100000,
            ])
            ->assertOk()
            ->assertJsonPath('shift.status', 'open')
            ->assertJsonPath('shift.cashier_name', $this->user->name)
            ->assertJsonPath('shift.opening_amount', 100000);

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.products', ['branch_id' => $this->branch->id]))
            ->assertOk();
    }

    public function test_overnight_operational_hours_are_supported(): void
    {
        $this->branch->apotekProfile()->update([
            'operational_hours' => $this->operationalHours([
                'thursday' => ['enabled' => true, 'open' => '20:00', 'close' => '02:00'],
            ]),
        ]);
        Carbon::setTestNow(Carbon::create(2026, 8, 27, 16, 0, 0, 'UTC'));

        $this->actingAs($this->user)
            ->get(route('penjualan.pos'))
            ->assertOk();

        Carbon::setTestNow(Carbon::create(2026, 8, 27, 20, 0, 0, 'UTC'));

        $this->actingAs($this->user)
            ->get(route('penjualan.pos'))
            ->assertRedirect(route('penjualan.pos.shifts'));
    }

    public function test_pos_is_blocked_when_profile_marks_today_as_closed(): void
    {
        $this->branch->apotekProfile()->update([
            'operational_hours' => $this->operationalHours([
                'thursday' => ['enabled' => false],
            ]),
        ]);

        $this->actingAs($this->user)
            ->postJson(route('penjualan.pos.shifts.open'), [
                'branch_id' => $this->branch->id,
                'opening_amount' => 100000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['operational_hours'])
            ->assertJsonPath('errors.operational_hours.0', 'Kasir/POS Cabang Shift tidak dapat dibuka karena Profile Apotek menetapkan Kamis sebagai hari tutup.');
    }

    public function test_pos_is_blocked_when_profile_apotek_hours_are_not_configured(): void
    {
        $this->branch->apotekProfile()->delete();

        $this->actingAs($this->user)
            ->get(route('penjualan.pos'))
            ->assertRedirect(route('penjualan.pos.shifts'))
            ->assertSessionHas('cashier_closed', 'Kasir/POS Cabang Shift tidak dapat dibuka karena jam operasional belum diatur di Profile Apotek.');

        $this->actingAs($this->user)
            ->postJson(route('penjualan.pos.shifts.open'), [
                'branch_id' => $this->branch->id,
                'opening_amount' => 100000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['operational_hours']);
    }

    public function test_cash_movements_and_closing_reconcile_the_drawer(): void
    {
        $open = $this->actingAs($this->user)->postJson(route('penjualan.pos.shifts.open'), [
            'branch_id' => $this->branch->id,
            'opening_amount' => 100000,
            'opening_notes' => 'Modal pecahan',
        ])->assertOk();
        $shiftId = (int) $open->json('shift.id');

        $transaction = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'cashier_shift_id' => $shiftId,
            'nomor_transaksi' => 'POS-SHIFT-001',
            'tanggal_transaksi' => now(),
            'status' => 'completed',
            'payment_status' => 'paid',
            'grand_total' => 50000,
            'total_bayar' => 55000,
            'kembalian' => 5000,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => now(),
        ]);
        PenjualanPaymentModel::create([
            'penjualan_transaction_id' => $transaction->id,
            'metode' => 'tunai',
            'amount' => 55000,
            'paid_at' => now(),
            'received_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->postJson(route('penjualan.pos.shifts.movement'), [
            'branch_id' => $this->branch->id,
            'type' => 'cash_in',
            'amount' => 10000,
            'description' => 'Tambahan uang kecil',
        ])->assertOk()->assertJsonPath('shift.expected_cash', 160000);

        $this->actingAs($this->user)->postJson(route('penjualan.pos.shifts.movement'), [
            'branch_id' => $this->branch->id,
            'type' => 'cash_out',
            'amount' => 3000,
            'description' => 'Biaya parkir kurir',
        ])->assertOk()
            ->assertJsonPath('shift.expected_cash', 157000)
            ->assertJsonCount(2, 'movements')
            ->assertJsonPath('movements.0.type', 'cash_out')
            ->assertJsonPath('movements.0.description', 'Biaya parkir kurir');

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.shifts.status', ['branch_id' => $this->branch->id]))
            ->assertOk()
            ->assertJsonCount(2, 'movements')
            ->assertJsonPath('movements.0.type_label', 'Kas Keluar')
            ->assertJsonPath('movements.0.created_by', $this->user->name)
            ->assertJsonPath('movements.0.occurred_at_label', '27/08/2026 · 10:00')
            ->assertJsonPath('movements.1.type_label', 'Kas Masuk');

        $this->actingAs($this->user)->postJson(route('penjualan.pos.shifts.close'), [
            'branch_id' => $this->branch->id,
            'actual_cash' => 156500,
            'closing_notes' => 'Kurang lima ratus',
        ])->assertOk()
            ->assertJsonPath('shift.status', 'closed')
            ->assertJsonPath('shift.cash_sales', 50000)
            ->assertJsonPath('shift.expected_cash', 157000)
            ->assertJsonPath('shift.cash_difference', -500);

        $this->assertDatabaseHas('cashier_shifts', [
            'id' => $shiftId,
            'status' => 'closed',
            'expected_cash' => 157000,
            'actual_cash' => 156500,
            'cash_difference' => -500,
        ]);
    }

    public function test_shift_can_be_closed_after_operational_hours(): void
    {
        $this->actingAs($this->user)->postJson(route('penjualan.pos.shifts.open'), [
            'branch_id' => $this->branch->id,
            'opening_amount' => 25000,
        ])->assertOk();

        Carbon::setTestNow(Carbon::create(2026, 8, 27, 14, 0, 0, 'UTC'));

        $this->actingAs($this->user)->postJson(route('penjualan.pos.shifts.close'), [
            'branch_id' => $this->branch->id,
            'actual_cash' => 25000,
        ])->assertOk()->assertJsonPath('shift.cash_difference', 0);

        $this->assertSame('closed', CashierShiftModel::first()->status);
    }

    private function operationalHours(array $overrides = []): array
    {
        $hours = collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ->mapWithKeys(fn (string $day) => [
                $day => [
                    'enabled' => true,
                    'open' => '08:00',
                    'close' => '20:00',
                ],
            ])
            ->all();

        foreach ($overrides as $day => $schedule) {
            $hours[$day] = array_merge($hours[$day], $schedule);
        }

        return $hours;
    }
}
