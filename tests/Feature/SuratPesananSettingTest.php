<?php

namespace Tests\Feature;

use App\Models\GolonganModel;
use App\Models\MainGolonganModel;
use App\Models\MasterObatModel;
use App\Models\SubGolonganModel;
use App\Models\SuratPesananSetting;
use App\Models\User;
use App\Services\Menu\PembelianPenerimaan\SuratPesananNarkotikaService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananPrekursorService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananRegulerService;
use App\Support\ControlledDrugClassification;
use App\Support\SidebarPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratPesananSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ControlledDrugClassification::flushSettingsCache();
    }

    public function test_the_most_specific_setting_determines_exactly_one_sp_type(): void
    {
        [$golongan, $mainGolongan, $subGolongan, $medicine] = $this->classificationTree();

        SuratPesananSetting::query()->create([
            'golongan_id' => $golongan->id,
            'sp_type' => SuratPesananSetting::TYPE_NARCOTIC,
        ]);
        SuratPesananSetting::query()->create([
            'sub_golongan_id' => $subGolongan->id,
            'sp_type' => SuratPesananSetting::TYPE_PRECURSOR,
        ]);

        $medicine->load(['golongan', 'mainGolongan.golongan', 'subGolongan.mainGolongan.golongan']);

        $this->assertFalse(app(SuratPesananNarkotikaService::class)->isNarcoticDrug($medicine));
        $this->assertTrue(app(SuratPesananPrekursorService::class)->isPrecursorDrug($medicine));
        $this->assertSame(
            'Sub Golongan: Turunan Khusus',
            app(SuratPesananPrekursorService::class)->matchedClassification($medicine)
        );
        $this->assertSame($mainGolongan->id, $medicine->main_golongan_id);
    }

    public function test_explicit_regular_setting_overrides_automatic_name_detection(): void
    {
        $golongan = GolonganModel::query()->create([
            'kode' => 'NAR',
            'nama' => 'Narkotika',
            'is_active' => true,
        ]);
        $medicine = MasterObatModel::query()->create([
            'kode_obat' => 'OBT-REG-001',
            'nama_obat' => 'Obat yang dikecualikan',
            'golongan_id' => $golongan->id,
        ]);
        SuratPesananSetting::query()->create([
            'golongan_id' => $golongan->id,
            'sp_type' => SuratPesananSetting::TYPE_REGULAR,
        ]);

        $medicine->load(['golongan', 'mainGolongan', 'subGolongan']);

        $this->assertFalse(app(SuratPesananNarkotikaService::class)->isNarcoticDrug($medicine));
        $this->assertTrue(app(SuratPesananRegulerService::class)->isRegularDrug($medicine));
    }

    public function test_authorized_user_can_save_setting_sp_from_the_settings_page(): void
    {
        [$golongan, $mainGolongan, $subGolongan] = $this->classificationTree();
        $user = User::factory()->create();
        $user->givePermissionTo(SidebarPermissions::SETTINGS_SURAT_PESANAN);

        $response = $this->actingAs($user)->putJson(route('settings.surat-pesanan.update'), [
            'assignments' => [
                ['level' => 'golongan', 'id' => $golongan->id, 'sp_type' => 'narkotika'],
                ['level' => 'main_golongan', 'id' => $mainGolongan->id, 'sp_type' => 'psikotropika'],
                ['level' => 'sub_golongan', 'id' => $subGolongan->id, 'sp_type' => 'oot'],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('summary.configured', 3);

        $this->assertDatabaseHas('surat_pesanan_settings', [
            'sub_golongan_id' => $subGolongan->id,
            'sp_type' => 'oot',
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('settings.surat-pesanan.index'))
            ->assertOk()
            ->assertSee('Setting SP')
            ->assertSee('Turunan Khusus');
    }

    /** @return array{GolonganModel, MainGolonganModel, SubGolonganModel, MasterObatModel} */
    private function classificationTree(): array
    {
        $golongan = GolonganModel::query()->create([
            'kode' => 'OBK-SP',
            'nama' => 'Obat Keras SP',
            'is_active' => true,
        ]);
        $mainGolongan = MainGolonganModel::query()->create([
            'golongan_id' => $golongan->id,
            'kode' => 'MAIN-SP',
            'nama' => 'Main Khusus',
        ]);
        $subGolongan = SubGolonganModel::query()->create([
            'main_golongan_id' => $mainGolongan->id,
            'kode' => 'SUB-SP',
            'nama' => 'Turunan Khusus',
        ]);
        $medicine = MasterObatModel::query()->create([
            'kode_obat' => 'OBT-SP-001',
            'nama_obat' => 'Obat Setting SP',
            'golongan_id' => $golongan->id,
            'main_golongan_id' => $mainGolongan->id,
            'sub_golongan_id' => $subGolongan->id,
        ]);

        return [$golongan, $mainGolongan, $subGolongan, $medicine];
    }
}
