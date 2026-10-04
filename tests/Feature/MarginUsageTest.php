<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\GolonganModel;
use App\Models\MainGolonganModel;
use App\Models\MarginsModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Stok\RiwayatHargaModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\SubGolonganModel;
use App\Models\User;
use App\Services\Settings\Margins\MarginsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MarginUsageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private BranchModel $branch;
    private MasterObatModel $medicine;
    private MarginsModel $margin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = BranchModel::create(['code' => 'MARGIN', 'name' => 'Cabang Margin', 'is_active' => true]);
        $this->user = User::factory()->create(['branch_id' => $this->branch->id]);
        $unit = SatuansModel::create(['kode' => 'MG', 'nama' => 'PCS', 'is_active' => true]);
        $group = GolonganModel::create(['kode' => 'MG', 'nama' => 'Golongan Margin']);
        $main = MainGolonganModel::create(['golongan_id' => $group->id, 'kode' => 'MG', 'nama' => 'Main Margin']);
        $sub = SubGolonganModel::create(['main_golongan_id' => $main->id, 'kode' => 'MG', 'nama' => 'Sub Margin']);
        $this->medicine = MasterObatModel::create([
            'kode_obat' => 'MG-001', 'nama_obat' => 'Obat Margin', 'satuan_id' => $unit->id,
            'golongan_id' => $group->id, 'main_golongan_id' => $main->id, 'sub_golongan_id' => $sub->id,
        ]);
        $this->margin = MarginsModel::create([
            'tingkat' => 'sub_golongan', 'reference_id' => $sub->id, 'faktor_jual' => 1.2, 'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    public function test_table_and_edit_mark_usage_and_count_distinct_products(): void
    {
        $this->get(route('margin.margin'))->assertOk()
            ->assertSee('Penerapan Perubahan Margin')->assertSee('Terapkan pada Penerimaan selanjutnya');
        $this->batch();
        $this->batch();
        $unused = MarginsModel::create([
            'tingkat' => 'golongan', 'reference_id' => $this->medicine->golongan_id, 'faktor_jual' => 1.1,
        ]);

        $rows = collect($this->getJson(route('margin.table'))->assertOk()->json('data'))->keyBy('id');
        $this->assertTrue($rows[$this->margin->id]['is_used']);
        $this->assertSame(1, $rows[$this->margin->id]['used_product_count']);
        $this->assertSame(2, $rows[$this->margin->id]['used_batch_count']);
        $this->assertFalse($rows[$unused->id]['is_used']);

        $this->getJson(route('margin.edit', $this->margin->id))->assertOk()
            ->assertJsonPath('is_used', true)->assertJsonPath('used_product_count', 1)
            ->assertJsonPath('used_batch_count', 2);
    }

    public function test_existing_option_reprices_only_used_stock_across_branches_and_records_history(): void
    {
        $first = $this->batch(['diskon' => 10, 'ppn' => 11, 'biaya_lain' => 50]);
        $second = $this->batch(['harga_beli' => 2000, 'biaya_lain' => 100]);
        $otherBranch = BranchModel::create(['code' => 'MARGIN-2', 'name' => 'Cabang Kedua']);
        $otherMedicine = $this->medicine->replicate()->fill(['kode_obat' => 'MG-002']);
        $otherMedicine->save();
        $third = $this->batch(['branch_id' => $otherBranch->id, 'obat_id' => $otherMedicine->id]);
        $untracked = $this->batch(['margin_id' => null, 'margin_factor' => null]);
        $empty = $this->batch(['qty' => 0]);
        $anotherRule = MarginsModel::create([
            'tingkat' => 'sub_golongan', 'reference_id' => $this->medicine->sub_golongan_id, 'faktor_jual' => 2,
        ]);
        $unrelated = $this->batch(['margin_id' => $anotherRule->id, 'margin_factor' => 2]);

        // Editing the reference must still target batches that used the old rule.
        $this->putJson(route('margin.update', $this->margin->id), array_merge($this->updatePayload('existing_products'), [
            'tingkat' => 'golongan', 'reference_id' => $this->medicine->golongan_id,
        ]))->assertOk()->assertJsonPath('updated_product_count', 2)->assertJsonPath('updated_batch_count', 3);

        $this->assertSame(1548.5, (float) $first->refresh()->harga_jual);
        $this->assertSame(3100.0, (float) $second->refresh()->harga_jual);
        $this->assertSame(1500.0, (float) $third->refresh()->harga_jual);
        foreach ([$untracked, $empty, $unrelated] as $unchanged) {
            $this->assertSame(1200.0, (float) $unchanged->refresh()->harga_jual);
        }
        $this->assertSame('1.500', $first->margin_factor);
        $this->assertSame('10.00', $first->qty);
        $this->assertNotNull($this->margin->refresh()->used_at);
        $this->assertDatabaseCount('riwayat_harga', 3);
        $this->assertDatabaseHas('riwayat_harga', [
            'stok_batch_id' => $first->id, 'harga_jual_lama' => 1200, 'harga_jual_baru' => 1548.5,
            'changed_by' => $this->user->id,
        ]);
        $reason = RiwayatHargaModel::where('stok_batch_id', $first->id)->firstOrFail()->alasan;
        $this->assertStringContainsString('Golongan "Golongan Margin"', $reason);
        $this->assertStringContainsString('20% (faktor jual 1,20) → 50% (faktor jual 1,50)', $reason);
        $this->assertStringContainsString('"Obat Margin" (batch "'.$first->no_batch.'")', $reason);
        $this->assertStringContainsString('Rp1.200,00 → Rp1.548,50', $reason);
        $this->assertStringNotContainsString('#'.$this->margin->id, $reason);
    }

    public function test_next_receipts_option_keeps_existing_price_and_changes_the_next_lookup(): void
    {
        $batch = $this->batch();
        $service = app(MarginsService::class);
        $this->assertSame(1.2, (float) $service->activeMarginForObat($this->medicine)->faktor_jual);

        $this->putJson(route('margin.update', $this->margin->id), $this->updatePayload('next_receipts'))
            ->assertOk()->assertJsonPath('application_scope', 'next_receipts')->assertJsonPath('updated_batch_count', 0);

        $this->assertSame(1200.0, (float) $batch->refresh()->harga_jual);
        $this->assertSame('1.200', $batch->margin_factor);
        $this->assertSame(1.5, (float) $service->activeMarginForObat($this->medicine)->faktor_jual);
        $this->assertDatabaseCount('riwayat_harga', 0);
    }

    public function test_update_requires_a_valid_application_choice_before_changing_any_price(): void
    {
        $batch = $this->batch();
        foreach ([null, 'invalid'] as $scope) {
            $payload = $this->updatePayload($scope ?? 'next_receipts');
            if ($scope === null) {
                unset($payload['application_scope']);
            }
            $this->putJson(route('margin.update', $this->margin->id), $payload)
                ->assertUnprocessable()->assertJsonValidationErrors('application_scope');
        }

        $this->assertSame(1.2, (float) $this->margin->refresh()->faktor_jual);
        $this->assertSame(1200.0, (float) $batch->refresh()->harga_jual);
        $this->assertDatabaseCount('riwayat_harga', 0);
    }

    public function test_price_history_failure_rolls_back_margin_and_all_batch_changes(): void
    {
        $batch = $this->batch();
        RiwayatHargaModel::creating(fn () => throw new RuntimeException('History failed'));
        $this->withoutExceptionHandling();

        try {
            $this->putJson(route('margin.update', $this->margin->id), $this->updatePayload('existing_products'));
            $this->fail('The history failure must propagate.');
        } catch (RuntimeException $exception) {
            $this->assertSame('History failed', $exception->getMessage());
            $this->assertSame(1.2, (float) $this->margin->refresh()->faktor_jual);
            $this->assertNull($this->margin->used_at);
            $this->assertSame(1200.0, (float) $batch->refresh()->harga_jual);
            $this->assertSame('1.200', $batch->margin_factor);
            $this->assertDatabaseCount('riwayat_harga', 0);
        } finally {
            RiwayatHargaModel::flushEventListeners();
        }
    }

    public function test_migration_backfills_only_legacy_batches_matching_the_active_margin_price(): void
    {
        $matching = $this->batch(['margin_id' => null, 'margin_factor' => null]);
        $manualPrice = $this->batch(['margin_id' => null, 'margin_factor' => null, 'harga_jual' => 1999]);
        $migration = require database_path('migrations/2026_10_04_000001_track_margin_usage_on_stock_batches.php');
        $migration->down();
        $migration->up();

        $this->assertSame($this->margin->id, $matching->refresh()->margin_id);
        $this->assertSame('1.200', $matching->margin_factor);
        $this->assertNull($manualPrice->refresh()->margin_id);
        $this->assertNotNull($this->margin->refresh()->used_at);
        $this->assertSame(1999.0, (float) $manualPrice->harga_jual);
    }

    public function test_usage_marker_survives_removal_of_a_previously_used_batch(): void
    {
        $batch = $this->batch();
        app(MarginsService::class)->markMarginUsed($this->margin);
        $batch->delete();

        $this->getJson(route('margin.edit', $this->margin->id))->assertOk()
            ->assertJsonPath('is_used', true)->assertJsonPath('used_batch_count', 0);
    }

    public function test_legacy_history_descriptions_are_clarified_without_changing_audit_values(): void
    {
        $batch = $this->batch();
        $legacy = RiwayatHargaModel::create([
            'obat_id' => $this->medicine->id, 'stok_batch_id' => $batch->id,
            'harga_jual_lama' => 1000, 'harga_jual_baru' => 1200, 'changed_by' => $this->user->id,
            'created_at' => '2026-10-04 08:00:00',
            'alasan' => 'Perubahan margin #'.$this->margin->id.' diterapkan pada produk yang telah menggunakan margin tersebut.',
        ]);
        $manual = $legacy->replicate()->fill(['alasan' => 'Penyesuaian harga oleh apoteker.']);
        $manual->save();
        $missingMargin = $legacy->replicate()->fill([
            'stok_batch_id' => null,
            'alasan' => 'Perubahan margin #99999 diterapkan pada produk yang telah menggunakan margin tersebut.',
        ]);
        $missingMargin->save();
        $original = $legacy->fresh()->getAttributes();
        unset($original['alasan']);

        $migration = require database_path('migrations/2026_10_04_000002_clarify_margin_price_history_descriptions.php');
        $migration->up();
        $legacy->refresh();
        $this->assertStringContainsString('Sub Golongan "Sub Margin"', $legacy->alasan);
        $this->assertStringContainsString('"Obat Margin" (batch "'.$batch->no_batch.'")', $legacy->alasan);
        $this->assertStringContainsString('Rp1.000,00 → Rp1.200,00', $legacy->alasan);
        $this->assertStringNotContainsString('faktor jual', $legacy->alasan);
        $this->assertStringNotContainsString('#', $legacy->alasan);
        $actual = $legacy->getAttributes();
        unset($actual['alasan']);
        $this->assertSame($original, $actual);
        $this->assertSame('Penyesuaian harga oleh apoteker.', $manual->refresh()->alasan);
        $this->assertStringContainsString('referensi tidak tersedia', $missingMargin->refresh()->alasan);
        $this->assertStringContainsString('batch tidak lagi tersedia', $missingMargin->alasan);

        $migration->up();
        $this->assertSame($legacy->alasan, $legacy->refresh()->alasan);
    }

    private function updatePayload(string $scope): array
    {
        return [
            'tingkat' => $this->margin->tingkat, 'reference_id' => $this->margin->reference_id,
            'faktor_jual' => 1.5, 'application_scope' => $scope,
        ];
    }

    private function batch(array $attributes = []): StokBatchModel
    {
        return StokBatchModel::create(array_merge([
            'branch_id' => $this->branch->id, 'obat_id' => $this->medicine->id,
            'no_batch' => 'MARGIN-'.(StokBatchModel::count() + 1), 'qty' => 10,
            'harga_beli' => 1000, 'biaya_lain' => 0, 'harga_jual' => 1200, 'diskon' => 0, 'ppn' => 0,
            'margin_id' => $this->margin->id, 'margin_factor' => 1.2,
        ], $attributes));
    }
}
