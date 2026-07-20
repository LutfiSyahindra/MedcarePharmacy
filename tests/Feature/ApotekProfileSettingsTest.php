<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApotekProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('settings.apotek-profile.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_manager_role_cannot_open_profile_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.apotek-profile.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('settings.apotek-profile.update'), [])
            ->assertForbidden();
    }

    public function test_admin_can_open_profile_settings_and_see_all_branches(): void
    {
        $admin = $this->userWithRole('Admin');
        $firstBranch = $this->branch('PUSAT', 'Apotek Pusat');
        $secondBranch = $this->branch('TIMUR', 'Apotek Timur');

        $this->actingAs($admin)
            ->get(route('settings.apotek-profile.index', ['branch_id' => $secondBranch->id]))
            ->assertOk()
            ->assertSee('Profile Apotek')
            ->assertSee($firstBranch->name)
            ->assertSee($secondBranch->name)
            ->assertSee('apotekLocationMap', false)
            ->assertSee('fadeAnimation: false', false)
            ->assertSee('https://tile.openstreetmap.org/{z}/{x}/{y}.png', false)
            ->assertSee('keepBuffer: 6', false)
            ->assertDontSee('integrity="sha256-', false)
            ->assertSee('Jam operasional');
    }

    public function test_apoteker_only_sees_and_updates_assigned_branches(): void
    {
        $apoteker = $this->userWithRole('Apoteker');
        $assignedBranch = $this->branch('ASSIGNED', 'Cabang Ditugaskan');
        $foreignBranch = $this->branch('FOREIGN', 'Cabang Rahasia');
        $apoteker->branches()->attach($assignedBranch->id);

        $this->actingAs($apoteker)
            ->get(route('settings.apotek-profile.index'))
            ->assertOk()
            ->assertSee($assignedBranch->name)
            ->assertDontSee($foreignBranch->name);

        $this->actingAs($apoteker)
            ->put(route('settings.apotek-profile.update'), $this->validPayload($foreignBranch))
            ->assertForbidden();

        $this->assertDatabaseMissing('apotek_profiles', ['branch_id' => $foreignBranch->id]);
    }

    public function test_admin_can_save_profile_location_hours_and_logo(): void
    {
        Storage::fake('public');

        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('SAVE', 'Cabang Simpan');
        $payload = $this->validPayload($branch, [
            'logo' => UploadedFile::fake()->image('logo-apotek.png', 500, 500),
        ]);

        $this->actingAs($admin)
            ->put(route('settings.apotek-profile.update'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('settings.apotek-profile.index', ['branch_id' => $branch->id]));

        $profile = ApotekProfile::where('branch_id', $branch->id)->firstOrFail();

        $this->assertSame('Apotek Harmoni Sehat', $profile->name);
        $this->assertSame('-6.2000000', $profile->latitude);
        $this->assertSame('106.8166667', $profile->longitude);
        $this->assertTrue($profile->operational_hours['monday']['enabled']);
        $this->assertFalse($profile->operational_hours['sunday']['enabled']);
        Storage::disk('public')->assertExists($profile->logo_path);
    }

    public function test_new_logo_replaces_and_deletes_previous_file(): void
    {
        Storage::fake('public');

        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('LOGO', 'Cabang Logo');
        Storage::disk('public')->put('apotek-logos/logo-lama.png', 'old-logo');
        ApotekProfile::create(array_merge(
            $this->profileAttributes($branch),
            ['logo_path' => 'apotek-logos/logo-lama.png']
        ));

        $this->actingAs($admin)
            ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
                'logo' => UploadedFile::fake()->image('logo-baru.webp', 400, 400),
            ]))
            ->assertSessionHasNoErrors();

        $profile = ApotekProfile::where('branch_id', $branch->id)->firstOrFail();

        Storage::disk('public')->assertMissing('apotek-logos/logo-lama.png');
        Storage::disk('public')->assertExists($profile->logo_path);
        $this->assertNotSame('apotek-logos/logo-lama.png', $profile->logo_path);
    }

    public function test_profile_requires_an_address_and_map_coordinates(): void
    {
        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('VALIDATE', 'Cabang Validasi');
        $payload = $this->validPayload($branch);
        unset($payload['address'], $payload['latitude'], $payload['longitude']);

        $this->actingAs($admin)
            ->from(route('settings.apotek-profile.index', ['branch_id' => $branch->id]))
            ->put(route('settings.apotek-profile.update'), $payload)
            ->assertRedirect(route('settings.apotek-profile.index', ['branch_id' => $branch->id]))
            ->assertSessionHasErrors(['address', 'latitude', 'longitude']);

        $this->assertDatabaseMissing('apotek_profiles', ['branch_id' => $branch->id]);
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate($roleName, 'web');
        $user->assignRole($role);

        return $user;
    }

    private function branch(string $code, string $name): BranchModel
    {
        return BranchModel::create([
            'code' => $code,
            'name' => $name,
            'address' => 'Jl. Sehat No. 1',
            'phone' => '0215551234',
            'email' => strtolower($code).'@example.test',
            'is_active' => true,
        ]);
    }

    private function validPayload(BranchModel $branch, array $overrides = []): array
    {
        return array_merge([
            'branch_id' => $branch->id,
            'name' => 'Apotek Harmoni Sehat',
            'slogan' => 'Melayani dengan sepenuh hati',
            'phone' => '0215551234',
            'whatsapp' => '6281234567890',
            'email' => 'halo@harmonisehat.test',
            'website' => 'https://harmonisehat.test',
            'instagram' => '@harmonisehat',
            'address' => 'Jl. Harmoni No. 10, Jakarta',
            'village' => 'Gambir',
            'district' => 'Gambir',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10110',
            'latitude' => -6.2,
            'longitude' => 106.8166667,
            'pharmacist_name' => 'apt. Siti Sehat, S.Farm.',
            'pharmacist_license_number' => 'SIPA-001',
            'pharmacy_license_number' => 'SIA-001',
            'license_expired_at' => '2028-12-31',
            'tax_id' => '00.000.000.0-000.000',
            'operational_hours' => $this->operationalHours(),
            'receipt_footer' => 'Terima kasih. Semoga lekas sehat.',
        ], $overrides);
    }

    private function profileAttributes(BranchModel $branch): array
    {
        $payload = $this->validPayload($branch);
        unset($payload['logo'], $payload['remove_logo']);

        return $payload;
    }

    private function operationalHours(): array
    {
        return collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ->mapWithKeys(fn (string $day) => [
                $day => [
                    'enabled' => $day !== 'sunday',
                    'open' => '08:00',
                    'close' => '21:00',
                ],
            ])
            ->all();
    }
}
