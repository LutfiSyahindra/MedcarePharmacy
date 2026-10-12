<?php

namespace Tests\Feature;

use App\Models\MasterObatModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportObatTradisionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->useStoragePath(base_path('storage/framework/testing/otl-'.uniqid()));
        $catalog = require database_path('data/obat_tradisional_2026_10_11.php');
        DB::table('golongan_obats')->insert(['kode' => 'OTL', 'nama' => 'OBAT TRADISIONAL', 'is_active' => true]);
        foreach (array_unique(array_column($catalog['profiles'], 'category')) as $code) {
            DB::table('categories')->insert(['code' => $code, 'name' => $code]);
        }
        foreach (array_unique($catalog['unit_codes']) as $code) {
            DB::table('satuans')->insert(['kode' => $code, 'nama' => $code, 'is_active' => true]);
        }
        foreach (array_unique(array_filter(array_column($catalog['profiles'], 'form'))) as $code) {
            if (str_starts_with($code, 'OTL-')) {
                continue; // Sediaan baru harus dibuat oleh impor.
            }
            DB::table('sediaan_obats')->insert(['kode' => $code, 'nama' => $code, 'is_active' => true]);
        }
        DB::table('pabrikan')->insert(['kode' => 'ULTRA', 'nama' => 'PT Ultra Sakti', 'is_active' => true]);
        DB::table('distributors')->insert(['kode' => 'MENSA', 'nama' => 'PT Mensa Binasukses', 'is_active' => true]);
    }

    public function test_preview_is_read_only(): void
    {
        $this->artisan('medcare:import-obat-tradisional')->assertSuccessful();

        $this->assertDatabaseCount('master_obats', 0);
        $this->assertDatabaseCount('pabrikan', 1);
        $this->assertDatabaseCount('distributors', 1);
        $this->assertFalse(File::isDirectory(storage_path('app/imports')));
    }

    public function test_import_keeps_all_products_in_requested_group_and_does_not_duplicate_on_rerun(): void
    {
        $this->artisan('medcare:import-obat-tradisional', ['--apply' => true])->assertSuccessful();
        $ids = MasterObatModel::query()->pluck('id')->all();

        $this->assertDatabaseCount('master_obats', 95);
        $this->assertDatabaseCount('obat_satuan_conversions', 95);
        $this->assertSame(95, DB::table('master_obats')->where('golongan_id', DB::table('golongan_obats')->value('id'))->count());
        $this->assertSame(0, DB::table('master_obats')->whereNotNull('main_golongan_id')->count());
        $this->assertSame(0, DB::table('master_obats')->whereNotNull('sub_golongan_id')->count());
        $this->assertSame(0, DB::table('master_obats')->whereNotNull('dosis')->count());
        $this->assertSame(0, DB::table('master_obats')->where('harga_beli', '!=', 0)->count());
        $this->assertSame(1, DB::table('pabrikan')->where('nama', 'PT Ultra Sakti')->count());
        $this->assertSame(1, DB::table('distributors')->where('nama', 'PT Mensa Binasukses')->count());
        $this->assertDatabaseMissing('distributors', ['nama' => 'OTC']);
        $this->assertDatabaseHas('sediaan_obats', ['kode' => 'OTL-PIL', 'nama' => 'Pil']);
        $this->assertDatabaseHas('sediaan_obats', ['kode' => 'OTL-PRM', 'nama' => 'Permen']);

        $capsule = MasterObatModel::query()->where('nama_obat', 'PSIDII CAP@50')->firstOrFail();
        $syrup = MasterObatModel::query()->where('nama_obat', 'PSIDII SYR 60ML')->firstOrFail();
        $this->assertStringContainsString('500 mg', $capsule->komposisi);
        $this->assertStringContainsString('Per 5 ml:', $syrup->komposisi);
        $this->assertStringContainsString('250 mg', $syrup->komposisi);
        $this->assertSame('HMT', $syrup->kategori->code);
        $this->assertSame('KPS', $capsule->satuan->kode);
        $this->assertNull(MasterObatModel::query()->where('nama_obat', 'KOMIX HERBAL KIDZ 10ML @4')->value('komposisi'));
        $this->assertNull(MasterObatModel::query()->where('nama_obat', 'EM KAPSUL')->value('komposisi'));
        $this->assertStringNotContainsString('kurma', mb_strtolower(MasterObatModel::query()->where('nama_obat', 'HABBATUSSAUDA KURMA AJWA@100 CAPS')->value('komposisi')));
        $this->assertStringContainsString('attapulgite', MasterObatModel::query()->where('nama_obat', 'DIAPET NR CAP@100')->value('komposisi'));
        $this->assertStringNotContainsString('attapulgite', MasterObatModel::query()->where('nama_obat', 'DIAPET ANAK SACH @6')->value('komposisi'));

        $audit = json_decode(File::get(File::files(storage_path('app/imports'))[0]->getPathname()), true, flags: JSON_THROW_ON_ERROR);
        $kapsida = collect($audit['items'])->firstWhere('name', 'KAPSIDA');
        $this->assertSame(['REZEKI HUSADA UTAMA, PT', 'SEJAHTERA SURYA INTRIO,PT'], $kapsida['distributors']);
        $this->assertSame('committed', $audit['status']);

        $this->artisan('medcare:import-obat-tradisional', ['--apply' => true])->assertSuccessful();
        $this->assertSame($ids, MasterObatModel::query()->pluck('id')->all());
        $this->assertDatabaseCount('obat_satuan_conversions', 95);
    }

    public function test_existing_medicine_and_its_commercial_data_are_preserved(): void
    {
        $existing = MasterObatModel::create([
            'kode_obat' => 'OLD-001', 'nama_obat' => 'madu hijau',
            'komposisi' => 'Formula label lama', 'harga_beli' => 12345,
        ]);

        $this->artisan('medcare:import-obat-tradisional', ['--apply' => true])->assertSuccessful();

        $this->assertDatabaseCount('master_obats', 95);
        $this->assertDatabaseHas('master_obats', [
            'id' => $existing->id, 'kode_obat' => 'OLD-001', 'harga_beli' => 12345,
            'komposisi' => 'Formula label lama', 'golongan_id' => null,
        ]);
    }
}
