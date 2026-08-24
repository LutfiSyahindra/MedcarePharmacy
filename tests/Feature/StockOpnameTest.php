<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\RakPenyimpananModel;
use App\Models\RoleSetting;
use App\Models\SatuansModel;
use App\Models\User;
use App\Services\Menu\Stok\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private RakPenyimpananModel $rack;

    private MasterObatModel $medicine;

    private StokBatchModel $batch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::create(['name' => 'Admin']));
        $this->branch = BranchModel::create(['code' => 'SO-TST', 'name' => 'Cabang Stock Opname', 'is_active' => true]);
        $this->user->branches()->attach($this->branch->id);
        $this->rack = RakPenyimpananModel::create(['kode' => 'RAK-SO', 'nama' => 'Rak Stock Opname', 'lokasi' => 'Gudang', 'is_active' => true]);
        $unit = SatuansModel::create(['kode' => 'PCS-SO', 'nama' => 'PCS', 'is_active' => true]);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'OBAT-SO-001', 'nama_obat' => 'Obat Stock Opname',
            'satuan_id' => $unit->id, 'rak_id' => $this->rack->id, 'harga_beli' => 5000, 'is_active' => true,
        ]);
        $this->batch = StokBatchModel::create([
            'branch_id' => $this->branch->id, 'obat_id' => $this->medicine->id,
            'no_batch' => 'SO-BATCH-01', 'expired_date' => now()->addYear()->toDateString(),
            'qty' => 10, 'harga_beli' => 5000, 'harga_jual' => 8000, 'diskon' => 0, 'ppn' => 0,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_counting_is_blind_and_locks_stock_and_cashier_for_the_branch(): void
    {
        $this->actingAs($this->user);
        $opnameId = $this->createOpname();
        $this->putJson(route('stockOpname.start', $opnameId))
            ->assertOk()->assertJsonPath('opname.status', 'counting')->assertJsonPath('opname.transaction_mode', 'freeze');

        $this->getJson(route('stockOpname.show', $opnameId))->assertOk()
            ->assertJsonPath('opname.comparison_visible', false)
            ->assertJsonMissingPath('opname.details.0.stok_sistem')
            ->assertJsonMissingPath('opname.details.0.selisih')
            ->assertJsonMissingPath('opname.details.0.hpp');
        $this->get(route('stok.stok'))->assertRedirect(route('stockOpname.index'));
        $this->getJson(route('stok.table'))->assertStatus(423)->assertJsonPath('stock_opname_id', $opnameId);
        $this->getJson(route('penjualan.pos.products', ['branch_id' => $this->branch->id]))
            ->assertStatus(423)->assertJsonPath('stock_opname_id', $opnameId);

        try {
            app(StockService::class)->recordManualMutation([
                'obat_id' => $this->medicine->id, 'stok_batch_id' => $this->batch->id,
                'qty' => 1, 'jenis_mutasi' => 'keluar',
            ]);
            $this->fail('Mutasi stok seharusnya ditolak selama blind count.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('dibekukan', $exception->errors()['stok'][0]);
        }
        $this->assertSame(10.0, (float) $this->batch->fresh()->qty);
    }

    public function test_first_submit_reveals_comparison_and_unlocks_operations(): void
    {
        $this->actingAs($this->user);
        [$opnameId, $detailId] = $this->startedOpname();
        $this->putJson(route('stockOpname.counts', $opnameId), ['details' => [['id' => $detailId, 'stok_fisik' => 8]]])->assertOk();
        $this->assertDatabaseHas('stock_opname_details', ['id' => $detailId, 'stok_fisik' => 8, 'stok_sistem_hitung' => null, 'selisih' => null]);
        $this->putJson(route('stockOpname.submit', $opnameId))->assertOk()->assertJsonPath('opname.status', 'awaiting_verification');
        $this->getJson(route('stockOpname.show', $opnameId))->assertOk()
            ->assertJsonPath('opname.comparison_visible', true)
            ->assertJsonPath('opname.details.0.stok_sistem', 10)
            ->assertJsonPath('opname.details.0.stok_fisik', 8)
            ->assertJsonPath('opname.details.0.selisih', -2)
            ->assertJsonPath('opname.details.0.hpp', 5000);
        $this->get(route('stok.stok'))->assertOk();

        app(StockService::class)->recordManualMutation([
            'obat_id' => $this->medicine->id, 'stok_batch_id' => $this->batch->id,
            'qty' => 1, 'jenis_mutasi' => 'keluar', 'keterangan' => 'Transaksi setelah submit',
        ]);
        $this->assertSame(9.0, (float) $this->batch->fresh()->qty);
        $this->assertDatabaseCount('stock_opname_movements', 1);
        $this->getJson(route('stockOpname.show', $opnameId))->assertOk()
            ->assertJsonPath('opname.movements.0.nama_obat', $this->medicine->nama_obat)
            ->assertJsonPath('opname.movements.0.kode_obat', $this->medicine->kode_obat)
            ->assertJsonPath('opname.movements.0.no_batch', $this->batch->no_batch)
            ->assertJsonPath('opname.movements.0.expired_date', $this->batch->expired_date->format('Y-m-d'));
    }

    public function test_assigned_branch_officer_can_submit_physical_stock_without_approver_role(): void
    {
        $this->actingAs($this->user);
        [$opnameId, $detailId] = $this->startedOpname();

        $officerRole = Role::create(['name' => 'Petugas Stock Opname', 'guard_name' => 'web']);
        $officer = User::factory()->create();
        $officer->branches()->attach($this->branch->id);
        $officer->assignRole($officerRole);

        $this->actingAs($officer)
            ->getJson(route('stockOpname.show', $opnameId))
            ->assertOk()
            ->assertJsonPath('opname.can_manage', false)
            ->assertJsonPath('opname.can_count', true)
            ->assertJsonPath('opname.can_submit', true);

        $this->putJson(route('stockOpname.counts', $opnameId), [
            'details' => [['id' => $detailId, 'stok_fisik' => 8]],
        ])->assertOk();

        $this->putJson(route('stockOpname.submit', $opnameId))
            ->assertOk()
            ->assertJsonPath('opname.status', 'awaiting_verification');

        $this->assertDatabaseHas('stock_opnames', [
            'id' => $opnameId,
            'status' => 'awaiting_verification',
            'submitted_by' => $officer->id,
        ]);
        $this->assertDatabaseHas('stock_opname_details', [
            'id' => $detailId,
            'counted_by' => $officer->id,
            'stok_fisik' => 8,
        ]);
        $this->getJson(route('stockOpname.show', $opnameId))
            ->assertOk()
            ->assertJsonPath('opname.submitted_by', $officer->name);
    }

    public function test_configured_role_can_view_stock_read_only_during_counting(): void
    {
        $this->actingAs($this->user);
        [$opnameId] = $this->startedOpname();

        $viewerRole = Role::create(['name' => 'Auditor Stok', 'guard_name' => 'web']);
        $viewer = User::factory()->create();
        $viewer->branches()->attach($this->branch->id);
        $viewer->assignRole($viewerRole);
        RoleSetting::create([
            'role_id' => $viewerRole->id,
            'can_view_stock_during_opname' => true,
        ]);

        $this->actingAs($viewer)
            ->get(route('stok.stok'))
            ->assertOk();
        $this->getJson(route('stok.table'))->assertOk();
        $this->postJson(route('stok.mutasi.store'), [])->assertStatus(423)
            ->assertJsonPath('stock_opname_id', $opnameId);
    }

    public function test_reason_is_required_and_validation_reconciles_running_transactions(): void
    {
        $this->actingAs($this->user);
        [$opnameId, $detailId] = $this->startedOpname();
        $this->putJson(route('stockOpname.counts', $opnameId), ['details' => [['id' => $detailId, 'stok_fisik' => 9]]])->assertOk();
        $this->putJson(route('stockOpname.submit', $opnameId))->assertOk();
        $this->putJson(route('stockOpname.verify', $opnameId))->assertUnprocessable()->assertJsonValidationErrors('alasan_selisih');
        $this->putJson(route('stockOpname.reasons', $opnameId), [
            'details' => [['id' => $detailId, 'alasan_selisih' => 'Satu unit rusak saat pemeriksaan.']],
        ])->assertOk();
        app(StockService::class)->recordManualMutation([
            'obat_id' => $this->medicine->id, 'stok_batch_id' => $this->batch->id,
            'qty' => 2, 'jenis_mutasi' => 'keluar', 'keterangan' => 'Penjualan setelah submit',
        ]);
        $this->putJson(route('stockOpname.verify', $opnameId), ['note' => 'Validasi transaksi berjalan.'])
            ->assertOk()->assertJsonPath('opname.status', 'awaiting_approval');
        $this->assertDatabaseHas('stock_opname_details', [
            'id' => $detailId, 'stok_sistem_hitung' => 10, 'stok_fisik' => 9, 'selisih' => -1,
            'mutasi_masuk' => 0, 'mutasi_keluar' => 2, 'stok_sistem_validasi' => 8,
            'stok_target_validasi' => 7, 'selisih_validasi' => -1,
            'alasan_selisih' => 'Satu unit rusak saat pemeriksaan.',
        ]);
    }

    public function test_assigned_branch_officer_can_validate_and_reconcile_without_approver_role(): void
    {
        $this->actingAs($this->user);
        [$opnameId, $detailId] = $this->startedOpname();
        $this->putJson(route('stockOpname.counts', $opnameId), [
            'details' => [['id' => $detailId, 'stok_fisik' => 9]],
        ])->assertOk();
        $this->putJson(route('stockOpname.submit', $opnameId))->assertOk();
        $this->putJson(route('stockOpname.reasons', $opnameId), [
            'details' => [['id' => $detailId, 'alasan_selisih' => 'Kemasan rusak.']],
        ])->assertOk();

        $officerRole = Role::create(['name' => 'Petugas Validasi Opname', 'guard_name' => 'web']);
        $officer = User::factory()->create();
        $officer->branches()->attach($this->branch->id);
        $officer->assignRole($officerRole);

        $this->actingAs($officer)
            ->getJson(route('stockOpname.show', $opnameId))
            ->assertOk()
            ->assertJsonPath('opname.can_verify', true)
            ->assertJsonPath('opname.can_approve', false);
        $this->putJson(route('stockOpname.verify', $opnameId), ['note' => 'Divalidasi petugas cabang.'])
            ->assertOk()
            ->assertJsonPath('opname.status', 'awaiting_approval')
            ->assertJsonPath('opname.verified_by', $officer->name);
        $this->assertDatabaseHas('stock_opnames', [
            'id' => $opnameId,
            'status' => 'awaiting_approval',
            'verified_by' => $officer->id,
        ]);
        $this->assertDatabaseHas('stock_opname_details', [
            'id' => $detailId,
            'stok_sistem_validasi' => 10,
            'stok_target_validasi' => 9,
            'selisih_validasi' => -1,
        ]);
        $this->putJson(route('stockOpname.approve', $opnameId))->assertForbidden();
    }

    public function test_posting_reconciles_late_transactions_and_saves_financial_summary(): void
    {
        $this->actingAs($this->user);
        [$opnameId, $detailId] = $this->startedOpname();
        $this->putJson(route('stockOpname.counts', $opnameId), ['details' => [['id' => $detailId, 'stok_fisik' => 9]]])->assertOk();
        $this->putJson(route('stockOpname.submit', $opnameId))->assertOk();
        $this->putJson(route('stockOpname.reasons', $opnameId), ['details' => [['id' => $detailId, 'alasan_selisih' => 'Barang rusak.']]])->assertOk();
        $this->putJson(route('stockOpname.verify', $opnameId))->assertOk();
        $this->putJson(route('stockOpname.approve', $opnameId))->assertOk();
        app(StockService::class)->recordManualMutation([
            'obat_id' => $this->medicine->id, 'stok_batch_id' => $this->batch->id,
            'qty' => 3, 'jenis_mutasi' => 'keluar', 'keterangan' => 'Kasir setelah validasi',
        ]);

        $this->putJson(route('stockOpname.adjust', $opnameId))->assertOk()
            ->assertJsonPath('opname.status', 'adjusted')
            ->assertJsonPath('summary.minus_qty', 1)
            ->assertJsonPath('summary.loss_value', 5000)
            ->assertJsonPath('summary.net_value', -5000);
        $this->assertSame(6.0, (float) $this->batch->fresh()->qty);
        $this->assertDatabaseHas('kartu_stok', [
            'stok_batch_id' => $this->batch->id, 'jenis_mutasi' => 'penyesuaian_opname_keluar',
            'qty_keluar' => 1, 'harga_beli' => 5000,
        ]);
    }

    public function test_print_sheet_never_contains_system_stock_or_hpp(): void
    {
        $this->actingAs($this->user);
        [$opnameId] = $this->startedOpname();
        $this->get(route('stockOpname.print', $opnameId))->assertOk()
            ->assertSee('LEMBAR PENGHITUNGAN STOCK OPNAME')->assertSee('Stok Fisik')
            ->assertSee($this->medicine->nama_obat)->assertDontSee('Stok Sistem')->assertDontSee('HPP');
    }

    public function test_authorized_reviewer_can_download_complete_pdf_after_submit(): void
    {
        $this->actingAs($this->user);
        [$opnameId, $detailId] = $this->startedOpname();

        $this->getJson(route('stockOpname.report', $opnameId))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->putJson(route('stockOpname.counts', $opnameId), [
            'details' => [['id' => $detailId, 'stok_fisik' => 9]],
        ])->assertOk();
        $this->putJson(route('stockOpname.submit', $opnameId))->assertOk();

        $this->getJson(route('stockOpname.show', $opnameId))
            ->assertOk()
            ->assertJsonPath('opname.report_url', route('stockOpname.report', $opnameId));

        $response = $this->get(route('stockOpname.report', $opnameId))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringContainsString(
            'laporan-stock-opname-',
            (string) $response->headers->get('Content-Disposition')
        );
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertGreaterThan(10_000, strlen($response->getContent()));
    }

    public function test_complete_pdf_is_forbidden_for_non_reviewer(): void
    {
        $this->actingAs($this->user);
        [$opnameId, $detailId] = $this->startedOpname();
        $this->putJson(route('stockOpname.counts', $opnameId), [
            'details' => [['id' => $detailId, 'stok_fisik' => 10]],
        ])->assertOk();
        $this->putJson(route('stockOpname.submit', $opnameId))->assertOk();

        $officer = User::factory()->create();
        $officer->branches()->attach($this->branch->id);

        $this->actingAs($officer)
            ->get(route('stockOpname.report', $opnameId))
            ->assertForbidden();
    }

    public function test_page_explains_the_revised_controlled_workflow(): void
    {
        $this->actingAs($this->user)->get(route('stockOpname.index'))->assertOk()
            ->assertSee('Blind Count')->assertSee('Stok dan POS dikunci hanya selama blind count')
            ->assertSee('Review Selisih')->assertSee('Summary')->assertSee('opnameDetailModal', false);
    }

    private function createOpname(): int
    {
        return (int) $this->postJson(route('stockOpname.store'), [
            'branch_id' => $this->branch->id, 'rak_id' => $this->rack->id,
            'tanggal_opname' => now()->toDateString(), 'catatan' => 'Pengujian alur revisi stock opname.',
        ])->assertOk()->assertJsonPath('opname.status', 'draft')->json('opname.id');
    }

    private function startedOpname(): array
    {
        $opnameId = $this->createOpname();
        $this->putJson(route('stockOpname.start', $opnameId))->assertOk();
        $detailId = (int) $this->getJson(route('stockOpname.show', $opnameId))->json('opname.details.0.id');

        return [$opnameId, $detailId];
    }
}
