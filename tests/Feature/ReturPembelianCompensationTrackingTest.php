<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReturPembelianCompensationTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_compensation_can_be_tracked_in_stages_and_cancelled_with_an_audit_trail(): void
    {
        [$user, $retur] = $this->postedReturn();

        $firstEntry = $this->actingAs($user)
            ->postJson(route('returPembelian.storeCompensation', $retur->id), [
                'tanggal_realisasi' => now()->format('Y-m-d'),
                'jenis' => 'barang_pengganti',
                'nominal' => 40000,
                'nomor_referensi' => 'SJ-GANTI-001',
                'keterangan' => 'Penggantian barang tahap pertama',
            ])
            ->assertOk()
            ->assertJsonPath('compensation.status', 'partial')
            ->assertJsonPath('compensation.received_value', 40000)
            ->assertJsonPath('compensation.outstanding_value', 60000)
            ->json('compensation.entries.0.id');

        $this->actingAs($user)
            ->postJson(route('returPembelian.storeCompensation', $retur->id), [
                'tanggal_realisasi' => now()->format('Y-m-d'),
                'jenis' => 'potongan_faktur',
                'nominal' => 60001,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nominal');

        $secondEntry = $this->actingAs($user)
            ->postJson(route('returPembelian.storeCompensation', $retur->id), [
                'tanggal_realisasi' => now()->format('Y-m-d'),
                'jenis' => 'potongan_faktur',
                'nominal' => 60000,
                'nomor_faktur' => 'INV-NEXT-001',
            ])
            ->assertOk()
            ->assertJsonPath('compensation.status', 'settled')
            ->assertJsonPath('compensation.outstanding_value', 0)
            ->json('compensation.entries.0.id');

        $this->actingAs($user)
            ->deleteJson(route('returPembelian.cancelCompensation', [$retur->id, $firstEntry]), [
                'cancellation_reason' => 'Surat jalan salah dicatat.',
            ])
            ->assertOk()
            ->assertJsonPath('compensation.status', 'partial')
            ->assertJsonPath('compensation.received_value', 60000)
            ->assertJsonPath('compensation.outstanding_value', 40000);

        $this->assertDatabaseHas('retur_pembelian_compensations', [
            'id' => $firstEntry,
            'cancellation_reason' => 'Surat jalan salah dicatat.',
        ]);

        $this->actingAs($user)
            ->putJson(route('returPembelian.updateCompensationPlan', $retur->id), [
                'expects_compensation' => false,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('expects_compensation');

        $this->actingAs($user)
            ->deleteJson(route('returPembelian.cancelCompensation', [$retur->id, $secondEntry]), [
                'cancellation_reason' => 'Potongan faktur dibatalkan supplier.',
            ])
            ->assertOk();

        $this->actingAs($user)
            ->putJson(route('returPembelian.updateCompensationPlan', $retur->id), [
                'expects_compensation' => false,
                'compensation_notes' => 'Retur disepakati tanpa kompensasi.',
            ])
            ->assertOk()
            ->assertJsonPath('compensation.status', 'not_required')
            ->assertJsonPath('compensation.outstanding_value', 0);

        $this->actingAs($user)
            ->getJson(route('returPembelian.show', $retur->id))
            ->assertOk()
            ->assertJsonPath('compensation.status', 'not_required')
            ->assertJsonCount(2, 'compensation.entries');
    }

    public function test_unpaid_supplier_compensation_is_marked_overdue_after_its_due_date(): void
    {
        [$user, $retur] = $this->postedReturn([
            'compensation_due_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->actingAs($user)
            ->getJson(route('returPembelian.show', $retur->id))
            ->assertOk()
            ->assertJsonPath('compensation.status', 'overdue')
            ->assertJsonPath('compensation.expected_value', 100000)
            ->assertJsonPath('compensation.received_value', 0)
            ->assertJsonPath('compensation.outstanding_value', 100000);
    }

    public function test_receiving_po_warns_when_supplier_has_unrealized_compensation(): void
    {
        [$user, $retur] = $this->postedReturn([
            'compensation_due_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $retur->compensations()->create([
            'tanggal_realisasi' => now()->format('Y-m-d'),
            'jenis' => 'potongan_faktur',
            'nominal' => 25000,
            'created_by' => $user->id,
        ]);

        $receivingPo = PembelianModel::create([
            'no_po' => 'PO-RECEIVE-COMP-'.uniqid(),
            'distributor_id' => $retur->distributor_id,
            'branch_id' => $retur->purchaseOrder->branch_id,
            'tanggal_po' => now()->format('Y-m-d'),
            'status' => 'approved',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->getJson(route('penerimaan.purchaseOrderDetail', $receivingPo->id))
            ->assertOk()
            ->assertJsonPath('supplier_compensation_alert.has_outstanding', true)
            ->assertJsonPath('supplier_compensation_alert.return_count', 1)
            ->assertJsonPath('supplier_compensation_alert.overdue_count', 1)
            ->assertJsonPath('supplier_compensation_alert.outstanding_value', 75000)
            ->assertJsonPath('supplier_compensation_alert.returns.0.nomor_retur', $retur->nomor_retur)
            ->assertJsonPath('supplier_compensation_alert.returns.0.status', 'overdue');
    }

    public function test_receipt_invoice_can_use_supplier_compensation_and_reverses_it_when_cancelled(): void
    {
        Notification::fake();
        [$user, $retur] = $this->postedReturn();
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $user->assignRole('Admin');

        $receivingPo = PembelianModel::create([
            'no_po' => 'PO-INVOICE-COMP-'.uniqid(),
            'distributor_id' => $retur->distributor_id,
            'branch_id' => $retur->purchaseOrder->branch_id,
            'tanggal_po' => now()->format('Y-m-d'),
            'status' => 'approved',
            'created_by' => $user->id,
        ]);
        $receipt = PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-INVOICE-COMP-'.uniqid(),
            'purchase_order_id' => $receivingPo->id,
            'distributor_id' => $retur->distributor_id,
            'nomor_faktur' => 'INV-INVOICE-COMP',
            'tanggal_penerimaan' => now()->format('Y-m-d'),
            'tanggal_faktur' => now()->format('Y-m-d'),
            'subtotal' => 100000,
            'grand_total' => 60000,
            'total_faktur' => 60000,
            'supplier_compensation_discount' => 40000,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        $allocation = $receipt->supplierCompensationAllocations()->create([
            'retur_pembelian_id' => $retur->id,
            'nominal' => 40000,
        ]);

        $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id))
            ->assertOk();

        $allocation->refresh();
        $this->assertNotNull($allocation->retur_pembelian_compensation_id);
        $this->assertDatabaseHas('retur_pembelian_compensations', [
            'id' => $allocation->retur_pembelian_compensation_id,
            'penerimaan_barang_id' => $receipt->id,
            'jenis' => 'potongan_faktur',
            'nominal' => 40000,
        ]);
        $this->assertSame(60000.0, $retur->fresh('compensations')->compensation_outstanding_value);

        $this->actingAs($user)
            ->putJson(route('penerimaan.cancel', $receipt->id))
            ->assertOk();

        $compensation = $allocation->compensation()->firstOrFail();
        $this->assertNotNull($compensation->cancelled_at);
        $this->assertSame(100000.0, $retur->fresh('compensations')->compensation_outstanding_value);
    }

    private function postedReturn(array $overrides = []): array
    {
        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-COMP-'.uniqid(),
            'name' => 'Cabang Compensation',
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);

        $distributor = DistributorModel::create([
            'kode' => 'DST-COMP-'.uniqid(),
            'nama' => 'Supplier Compensation',
            'is_active' => true,
        ]);
        $purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-COMP-'.uniqid(),
            'distributor_id' => $distributor->id,
            'branch_id' => $branch->id,
            'tanggal_po' => now()->format('Y-m-d'),
            'status' => 'approved',
            'created_by' => $user->id,
        ]);
        $receipt = PenerimaanBarangModel::create([
            'nomor_penerimaan' => 'PB-COMP-'.uniqid(),
            'purchase_order_id' => $purchaseOrder->id,
            'distributor_id' => $distributor->id,
            'tanggal_penerimaan' => now()->format('Y-m-d'),
            'grand_total' => 100000,
            'status' => 'posted',
            'created_by' => $user->id,
        ]);
        $retur = ReturPembelianModel::create(array_merge([
            'nomor_retur' => 'RPB-COMP-'.uniqid(),
            'penerimaan_barang_id' => $receipt->id,
            'purchase_order_id' => $purchaseOrder->id,
            'distributor_id' => $distributor->id,
            'tanggal_retur' => now()->format('Y-m-d'),
            'grand_total' => 100000,
            'status' => 'posted',
            'expects_compensation' => true,
            'created_by' => $user->id,
            'posted_by' => $user->id,
            'posted_at' => now(),
        ], $overrides));

        return [$user, $retur];
    }
}
