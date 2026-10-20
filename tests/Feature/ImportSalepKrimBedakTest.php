<?php

namespace Tests\Feature;

use App\Models\MasterObatModel;
use App\Services\Settings\Master\MasterObatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class ImportSalepKrimBedakTest extends TestCase
{
    use RefreshDatabase;

    private int $groupId;

    private array $mainIds;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->useStoragePath(base_path('storage/framework/testing/skb-'.uniqid()));
        $catalog = require database_path('data/salep_krim_bedak_2026_10_17.php');
        $this->groupId = DB::table('golongan_obats')->insertGetId(['kode' => 'SKB', 'nama' => $catalog['group'], 'is_active' => true]);
        foreach ($catalog['main_names'] as $code => $name) {
            $this->mainIds[$code] = DB::table('main_golongan_obats')->insertGetId(['golongan_id' => $this->groupId, 'kode' => $code, 'nama' => $name]);
        }
        foreach (array_unique([...array_column($catalog['rules'], 'category'), 'HRM']) as $code) {
            DB::table('categories')->insert(['code' => $code, 'name' => $code]);
        }
        foreach (['TUB', 'BTL', 'JAR', 'PC', 'SACH', 'BOX', 'TBL'] as $code) {
            DB::table('satuans')->insert(['kode' => $code, 'nama' => $code, 'is_active' => true]);
        }
        foreach (['SED-001', 'SED-039', 'SED-040', 'SED-041', 'SED-043', 'SED-045', 'SED-047', 'SKB-S-CB090E25B3'] as $code) {
            DB::table('sediaan_obats')->insert(['kode' => $code, 'nama' => $code, 'is_active' => true]);
        }
    }

    public function test_preview_does_not_change_medicines_references_or_audits(): void
    {
        $medicine = $this->medicine('BENOSON N 15 GR');
        $before = $medicine->getAttributes();

        $this->artisan('medcare:import-salep-krim-bedak')->assertSuccessful();

        $this->assertSame($before, $medicine->fresh()->getAttributes());
        $this->assertDatabaseCount('master_obats', 1);
        $this->assertDatabaseCount('pabrikan', 0);
        $this->assertDatabaseCount('distributors', 0);
        $this->assertFalse(File::isDirectory(storage_path('app/imports')));
    }

    public function test_import_matches_aliases_keeps_sizes_separate_and_fills_only_missing_main(): void
    {
        $old = $this->medicine('BENOSON N 15 GR');
        $this->medicine('DERMOVATE CR 5 GR');
        $preserved = $this->medicine('THROMBOPHOB GEL', ['main_golongan_id' => $this->mainIds['SLPBT']]);
        $generic = $this->medicine('BENOSON CREAM'); // Ukuran belum diketahui: bukan padanan 5 g atau 15 g.
        $this->medicine('ACIFAR CREAM 5 GR');
        $quasi = $this->medicine('SALEP HITAM ICHTYOL 12GR');
        $otherGroup = DB::table('golongan_obats')->insertGetId(['kode' => 'OTH', 'nama' => 'LAINNYA']);
        $outside = $this->medicine('PARACETAMOL', ['golongan_id' => $otherGroup]);
        $outsideBefore = $outside->getAttributes();
        $preservedBefore = $preserved->getAttributes();
        $oldBefore = $old->getAttributes();

        $this->artisan('medcare:import-salep-krim-bedak', ['--apply' => true])->assertSuccessful();

        $this->assertDatabaseCount('master_obats', 150);
        $this->assertSame($this->mainIds['SLPK'], $old->fresh()->main_golongan_id);
        $this->assertSame($this->mainIds['SLPK'], $generic->fresh()->main_golongan_id);
        $this->assertSame($this->mainIds['SLPKB'], $quasi->fresh()->main_golongan_id);
        $this->assertSame($oldBefore, array_replace($old->fresh()->getAttributes(), ['main_golongan_id' => null, 'updated_at' => $oldBefore['updated_at']]));
        $this->assertSame($preservedBefore, $preserved->fresh()->getAttributes());
        $this->assertSame($outsideBefore, $outside->fresh()->getAttributes());
        $this->assertDatabaseMissing('master_obats', ['nama_obat' => 'BENOSON N CR 15GR']);
        $this->assertDatabaseHas('master_obats', ['nama_obat' => 'BENOSON CR 15GR']);
        $this->assertDatabaseHas('master_obats', ['nama_obat' => 'BENOSON CR 5GR']);
        $this->assertSame(0, DB::table('master_obats')->where('golongan_id', $this->groupId)->whereNull('main_golongan_id')->count());
        foreach ([
            'BENZOLAC 2.5% GEL 5GR' => 'SLPBT',
            'BENZOLAC-CL GEL 10GR' => 'SLPK',
            'CARMED CR 10% 40GR' => 'SLPK',
            'CARMED LOT 100ML' => 'SLPKB',
            'KETOMED 2% GEL 15GR' => 'SLPBT',
            'CANESTEN PRO CREAM 15 G' => 'SLPBT',
            'CANESTEN SD VAGINA TAB@1' => 'SLPK',
            'MARCKS ACTIVE CREME 20GR (NEW)' => 'SLPKB',
        ] as $name => $main) {
            $this->assertDatabaseHas('master_obats', ['nama_obat' => $name, 'golongan_id' => $this->groupId, 'main_golongan_id' => $this->mainIds[$main]]);
        }
        $powder = MasterObatModel::where('nama_obat', 'MARCKS ACTIVE CREME 20GR (NEW)')->firstOrFail();
        $this->assertSame('SED-045', $powder->sediaan->kode);
        $this->assertSame('JAR', $powder->satuan->kode);
        $this->assertDatabaseCount('obat_satuan_conversions', 143);

        $beforeRerun = DB::table('master_obats')->orderBy('id')->get()->toJson();
        $this->artisan('medcare:import-salep-krim-bedak', ['--apply' => true])->assertSuccessful();
        $this->assertSame($beforeRerun, DB::table('master_obats')->orderBy('id')->get()->toJson());
        $this->assertDatabaseCount('obat_satuan_conversions', 143);
        $audit = json_decode(File::get(File::files(storage_path('app/imports'))[0]->getPathname()), true);
        $this->assertSame('committed', $audit['status']);
        $this->assertCount(146, $audit['result']['items']);
    }

    public function test_unknown_product_prevents_all_mutations(): void
    {
        $this->medicine('NAMA BELUM DIPETAKAN');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Produk belum memiliki pemetaan');
        try {
            $this->artisan('medcare:import-salep-krim-bedak', ['--apply' => true]);
        } finally {
            $this->assertDatabaseCount('master_obats', 1);
            $this->assertDatabaseCount('pabrikan', 0);
            $this->assertDatabaseCount('distributors', 0);
        }
    }

    public function test_existing_source_in_another_group_is_reclassified_without_duplication(): void
    {
        $otherGroup = DB::table('golongan_obats')->insertGetId(['kode' => 'SHP', 'nama' => 'SAMPO']);
        $otherMain = DB::table('main_golongan_obats')->insertGetId(['golongan_id' => $otherGroup, 'kode' => 'SHP-M', 'nama' => 'SAMPO OBAT']);
        $otherSub = DB::table('sub_golongan_obats')->insertGetId(['main_golongan_id' => $otherMain, 'kode' => 'SHP-S', 'nama' => 'SAMPO']);
        $medicine = $this->medicine('KETOMED 2% SCALP LOT 60 ML', [
            'golongan_id' => $otherGroup, 'main_golongan_id' => $otherMain, 'sub_golongan_id' => $otherSub,
        ]);

        $this->artisan('medcare:import-salep-krim-bedak', ['--apply' => true])->assertSuccessful();

        $this->assertDatabaseCount('master_obats', 146);
        $this->assertDatabaseHas('master_obats', [
            'id' => $medicine->id, 'kode_obat' => $medicine->kode_obat, 'harga_beli' => 12345,
            'komposisi' => 'Formula lama', 'golongan_id' => $this->groupId,
            'main_golongan_id' => $this->mainIds['SLPBT'], 'sub_golongan_id' => null,
        ]);
        $this->assertDatabaseMissing('master_obats', ['nama_obat' => 'KETOMED 2% SCALP LOT 60ML']);
    }

    public function test_failure_creating_medicines_rolls_back_backfill(): void
    {
        $medicine = $this->medicine('BENOSON N 15 GR');
        $this->mock(MasterObatService::class, function ($mock) {
            $mock->shouldReceive('createMasterObat')->once()->andThrow(new RuntimeException('Simulasi kegagalan simpan'));
        });
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Simulasi kegagalan simpan');
        try {
            $this->artisan('medcare:import-salep-krim-bedak', ['--apply' => true]);
        } finally {
            $this->assertNull($medicine->fresh()->main_golongan_id);
            $this->assertDatabaseCount('master_obats', 1);
            $this->assertDatabaseCount('pabrikan', 0);
            $this->assertDatabaseCount('distributors', 0);
        }
    }

    private function medicine(string $name, array $overrides = []): MasterObatModel
    {
        return MasterObatModel::create(array_replace([
            'kode_obat' => 'OLD-'.(MasterObatModel::count() + 1),
            'nama_obat' => $name, 'golongan_id' => $this->groupId,
            'main_golongan_id' => null, 'komposisi' => 'Formula lama', 'harga_beli' => 12345,
        ], $overrides))->fresh();
    }
}
