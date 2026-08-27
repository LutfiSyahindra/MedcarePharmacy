<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\PatientModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'CB-PST',
            'name' => 'Cabang Pasien',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_patient_menu_requires_authentication(): void
    {
        $this->get(route('pasien.index'))->assertRedirect(route('login'));
        $this->getJson(route('pasien.visits'))->assertUnauthorized();
    }

    public function test_user_can_store_patient_with_name_and_phone_only(): void
    {
        $this->actingAs($this->user)
            ->get(route('pasien.index'))
            ->assertOk()
            ->assertSee('Data Pasien')
            ->assertSee('nama dan nomor telepon pasien')
            ->assertSee('Kunjungan Pasien')
            ->assertSee('data-visit-period="year"', false)
            ->assertSee($this->branch->name);

        $this->actingAs($this->user)
            ->postJson(route('pasien.store'), [
                'branch_id' => $this->branch->id,
                'name' => 'Siti Rahma',
                'phone' => '+62 812-3456-7890',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Siti Rahma')
            ->assertJsonPath('data.phone', '081234567890');

        $this->assertDatabaseHas('patients', [
            'branch_id' => $this->branch->id,
            'name' => 'Siti Rahma',
            'phone' => '081234567890',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_patient_can_be_searched_from_cashier(): void
    {
        $patient = $this->createPatient();

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.patients', [
                'branch_id' => $this->branch->id,
                'q' => '081234',
            ]))
            ->assertOk()
            ->assertJsonPath('results.0.id', $patient->id)
            ->assertJsonPath('results.0.name', 'Siti Rahma')
            ->assertJsonPath('results.0.phone', '081234567890');
    }

    public function test_patient_can_be_updated_and_deleted_without_deleting_transaction_snapshots(): void
    {
        $patient = $this->createPatient();
        $transaction = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'patient_id' => $patient->id,
            'nomor_transaksi' => 'POS-PATIENT-001',
            'tanggal_transaksi' => now(),
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
            'payment_status' => 'paid',
            'customer_name' => $patient->name,
            'customer_phone' => $patient->phone,
        ]);

        $this->actingAs($this->user)
            ->putJson(route('pasien.update', $patient), [
                'branch_id' => $this->branch->id,
                'name' => 'Siti Rahmawati',
                'phone' => '081299999999',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Siti Rahmawati')
            ->assertJsonPath('data.phone', '081299999999');

        $this->actingAs($this->user)
            ->deleteJson(route('pasien.destroy', $patient))
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
        $this->assertNull($transaction->fresh()->patient_id);
        $this->assertSame('Siti Rahma', $transaction->fresh()->customer_name);
        $this->assertSame('081234567890', $transaction->fresh()->customer_phone);
    }

    public function test_name_and_valid_phone_are_required(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('pasien.store'), [
                'branch_id' => $this->branch->id,
                'name' => '',
                'phone' => '123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_phone_must_be_unique_per_branch(): void
    {
        $this->createPatient();

        $this->actingAs($this->user)
            ->postJson(route('pasien.store'), [
                'branch_id' => $this->branch->id,
                'name' => 'Pasien Duplikat',
                'phone' => '+62 812 3456 7890',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_user_cannot_access_patients_from_another_pos_branch(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'CB-LAIN',
            'name' => 'Cabang Lain',
            'is_active' => true,
        ]);
        $foreignPatient = PatientModel::create([
            'branch_id' => $foreignBranch->id,
            'name' => 'Pasien Cabang Lain',
            'phone' => '081300000001',
        ]);

        $this->actingAs($this->user)
            ->getJson(route('pasien.show', $foreignPatient))
            ->assertNotFound();

        $this->actingAs($this->user)
            ->getJson(route('penjualan.pos.patients', ['branch_id' => $foreignBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_patient_visits_can_be_viewed_by_week_month_and_year(): void
    {
        Carbon::setTestNow('2026-08-27 12:00:00');
        $patient = $this->createPatient();
        $secondPatient = PatientModel::create([
            'branch_id' => $this->branch->id,
            'name' => 'Budi Sehat',
            'phone' => '081355555555',
        ]);

        $this->createVisit($patient, '2026-08-25 09:00:00', 'VISIT-WEEK-1');
        $this->createVisit($patient, '2026-08-26 09:00:00', 'VISIT-WEEK-2');
        $this->createVisit($secondPatient, '2026-08-05 09:00:00', 'VISIT-MONTH');
        $this->createVisit($secondPatient, '2026-01-15 09:00:00', 'VISIT-YEAR');
        $this->createVisit($patient, '2026-08-27 10:00:00', 'VISIT-DRAFT', 'draft');
        $this->createVisit(null, '2026-08-27 11:00:00', 'VISIT-GENERAL');

        $week = $this->actingAs($this->user)
            ->getJson(route('pasien.visits', ['period' => 'week']));

        $week->assertOk()
            ->assertJsonPath('data.meta.period', 'week')
            ->assertJsonPath('data.meta.start_date', '2026-08-24')
            ->assertJsonPath('data.summary.visits', 2)
            ->assertJsonPath('data.summary.unique_patients', 1)
            ->assertJsonPath('data.summary.repeat_visits', 1)
            ->assertJsonPath('data.top_patients.0.id', $patient->id)
            ->assertJsonCount(4, 'data.trend.labels');

        $this->actingAs($this->user)
            ->getJson(route('pasien.visits', ['period' => 'month']))
            ->assertOk()
            ->assertJsonPath('data.summary.visits', 3)
            ->assertJsonPath('data.summary.unique_patients', 2)
            ->assertJsonPath('data.summary.repeat_visits', 1);

        $this->actingAs($this->user)
            ->getJson(route('pasien.visits', ['period' => 'year']))
            ->assertOk()
            ->assertJsonPath('data.summary.visits', 4)
            ->assertJsonPath('data.summary.unique_patients', 2)
            ->assertJsonPath('data.summary.repeat_visits', 2)
            ->assertJsonCount(8, 'data.trend.labels');
    }

    public function test_patient_visit_analytics_rejects_an_unassigned_branch(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'CB-VISIT-FOREIGN',
            'name' => 'Cabang Kunjungan Lain',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('pasien.visits', ['branch_id' => $foreignBranch->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');
    }

    private function createPatient(): PatientModel
    {
        return PatientModel::create([
            'branch_id' => $this->branch->id,
            'name' => 'Siti Rahma',
            'phone' => '081234567890',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
    }

    private function createVisit(?PatientModel $patient, string $visitedAt, string $number, string $status = 'completed'): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'patient_id' => $patient?->id,
            'nomor_transaksi' => $number,
            'tanggal_transaksi' => $visitedAt,
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => $status,
            'payment_status' => $status === 'completed' ? 'paid' : 'unpaid',
            'customer_name' => $patient?->name ?: 'Pelanggan Umum',
            'customer_phone' => $patient?->phone,
        ]);
    }
}
