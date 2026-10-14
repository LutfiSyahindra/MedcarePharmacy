<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
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
        $this->assertSame('081234567890', $profile->pharmacist_phone);
        $this->assertSame('-6.2000000', $profile->latitude);
        $this->assertSame('106.8166667', $profile->longitude);
        $this->assertTrue($profile->operational_hours['monday']['enabled']);
        $this->assertFalse($profile->operational_hours['sunday']['enabled']);
        Storage::disk('public')->assertExists($profile->logo_path);
    }

    public function test_pharmacist_phone_is_displayed_updated_and_cleared_only_for_the_selected_branch(): void
    {
        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('PHONE', 'Cabang Telepon');
        $otherBranch = $this->branch('OTHER', 'Cabang Lain');
        $profile = ApotekProfile::create($this->profileAttributes($branch));
        $otherProfile = ApotekProfile::create($this->profileAttributes($otherBranch));

        $this->actingAs($admin)
            ->get(route('settings.apotek-profile.index', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('Nomor telepon apoteker')
            ->assertSee('name="pharmacist_phone"', false)
            ->assertSee('value="081234567890"', false);

        $this->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
            'pharmacist_phone' => '+62 812-3456-7891',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('+62 812-3456-7891', $profile->fresh()->pharmacist_phone);
        $this->assertSame('081234567890', $otherProfile->fresh()->pharmacist_phone);

        $this->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
            'pharmacist_phone' => '',
        ]))->assertSessionHasNoErrors();

        $this->assertNull($profile->fresh()->pharmacist_phone);
        $this->assertSame('081234567890', $otherProfile->fresh()->pharmacist_phone);
    }

    public function test_pharmacist_phone_longer_than_thirty_characters_is_rejected(): void
    {
        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('PHONE-LIMIT', 'Cabang Validasi Telepon');

        $this->actingAs($admin)
            ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
                'pharmacist_phone' => str_repeat('1', 31),
            ]))
            ->assertSessionHasErrors(['pharmacist_phone']);

        $this->assertDatabaseMissing('apotek_profiles', ['branch_id' => $branch->id]);
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

    public function test_signature_is_saved_and_previewed_for_the_selected_branch(): void
    {
        Storage::fake('public');

        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('SIGN', 'Cabang Tanda Tangan');
        $otherBranch = $this->branch('OTHER', 'Cabang Lain');

        $this->actingAs($admin)
            ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
                'pharmacist_signature' => UploadedFile::fake()->image('tanda-tangan.png', 600, 200),
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('settings.apotek-profile.index', ['branch_id' => $branch->id]));

        $profile = ApotekProfile::where('branch_id', $branch->id)->firstOrFail();
        $this->assertStringStartsWith('apotek-signatures/', $profile->pharmacist_signature_path);
        Storage::disk('public')->assertExists($profile->pharmacist_signature_path);

        $this->get(route('settings.apotek-profile.index', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('Tanda tangan apoteker')
            ->assertSee($profile->pharmacist_signature_url);
        $this->get(route('settings.apotek-profile.index', ['branch_id' => $otherBranch->id]))
            ->assertOk()
            ->assertDontSee($profile->pharmacist_signature_url);
    }

    public function test_signature_is_preserved_when_other_profile_fields_are_updated(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('KEEP', 'Cabang Simpan Tanda Tangan');
        $signaturePath = 'apotek-signatures/saved.png';
        Storage::disk('public')->put($signaturePath, 'saved-signature');
        $profile = ApotekProfile::create($this->profileAttributes($branch) + [
            'pharmacist_signature_path' => $signaturePath,
        ]);

        $this->actingAs($admin)
            ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, ['phone' => '0211234567']))
            ->assertSessionHasNoErrors();

        $this->assertSame($signaturePath, $profile->fresh()->pharmacist_signature_path);
        Storage::disk('public')->assertExists($signaturePath);
    }

    public function test_new_signature_replaces_and_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('REPLACE', 'Cabang Ganti Tanda Tangan');
        $oldPath = 'apotek-signatures/old.png';
        Storage::disk('public')->put($oldPath, 'old-signature');
        $profile = ApotekProfile::create($this->profileAttributes($branch) + [
            'pharmacist_signature_path' => $oldPath,
        ]);

        $this->actingAs($admin)
            ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
                'pharmacist_signature' => UploadedFile::fake()->image('new.png', 600, 200),
                'remove_pharmacist_signature' => '1',
            ]))
            ->assertSessionHasNoErrors();

        $newPath = $profile->fresh()->pharmacist_signature_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_signature_can_be_removed_without_removing_the_logo(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('REMOVE', 'Cabang Hapus Tanda Tangan');
        $signaturePath = 'apotek-signatures/old.png';
        $logoPath = 'apotek-logos/logo.png';
        Storage::disk('public')->put($signaturePath, 'signature');
        Storage::disk('public')->put($logoPath, 'logo');
        $profile = ApotekProfile::create($this->profileAttributes($branch) + [
            'pharmacist_signature_path' => $signaturePath,
            'logo_path' => $logoPath,
        ]);

        $this->actingAs($admin)
            ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
                'remove_pharmacist_signature' => '1',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertNull($profile->fresh()->pharmacist_signature_path);
        Storage::disk('public')->assertMissing($signaturePath);
        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_invalid_or_oversized_signature_upload_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('INVALID', 'Cabang Validasi Tanda Tangan');
        $files = [
            UploadedFile::fake()->create('signature.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('signature.gif'),
            UploadedFile::fake()->image('signature.png')->size(2049),
        ];

        foreach ($files as $file) {
            $this->actingAs($admin)
                ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
                    'pharmacist_signature' => $file,
                ]))
                ->assertSessionHasErrors('pharmacist_signature');
        }

        $this->assertDatabaseMissing('apotek_profiles', ['branch_id' => $branch->id]);
        $this->assertSame([], Storage::disk('public')->allFiles('apotek-signatures'));
    }

    public function test_apoteker_cannot_upload_a_signature_to_an_unassigned_branch(): void
    {
        Storage::fake('public');
        $apoteker = $this->userWithRole('Apoteker');
        $branch = $this->branch('UNASSIGNED', 'Cabang Tanpa Akses');

        $this->actingAs($apoteker)
            ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
                'pharmacist_signature' => UploadedFile::fake()->image('signature.png'),
            ]))
            ->assertForbidden();

        $this->assertDatabaseMissing('apotek_profiles', ['branch_id' => $branch->id]);
        $this->assertSame([], Storage::disk('public')->allFiles('apotek-signatures'));
    }

    public function test_failed_profile_save_cleans_new_uploads_and_preserves_the_saved_signature(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('Admin');
        $branch = $this->branch('FAIL', 'Cabang Gagal Simpan');
        $oldPath = 'apotek-signatures/old.png';
        Storage::disk('public')->put($oldPath, 'old-signature');
        $profile = ApotekProfile::create($this->profileAttributes($branch) + [
            'pharmacist_signature_path' => $oldPath,
        ]);
        $event = 'eloquent.saving: '.ApotekProfile::class;
        Event::listen($event, function () {
            throw new \RuntimeException('Simulated save failure');
        });

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)
                ->put(route('settings.apotek-profile.update'), $this->validPayload($branch, [
                    'logo' => UploadedFile::fake()->image('logo.png'),
                    'pharmacist_signature' => UploadedFile::fake()->image('signature.png'),
                ]));
            $this->fail('The simulated save failure was not thrown.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated save failure', $exception->getMessage());
        } finally {
            Event::forget($event);
        }

        $this->assertSame($oldPath, $profile->fresh()->pharmacist_signature_path);
        $this->assertSame([$oldPath], Storage::disk('public')->allFiles());
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
            'pharmacist_phone' => '081234567890',
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
