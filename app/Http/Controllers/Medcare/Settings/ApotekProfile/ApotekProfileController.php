<?php

namespace App\Http\Controllers\Medcare\Settings\ApotekProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateApotekProfileRequest;
use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApotekProfileController extends Controller
{
    private const MANAGER_ROLES = ['Admin', 'admin', 'Apoteker', 'apoteker'];

    private const DAY_LABELS = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ];

    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($this->userCanManage($user), 403);

        $branches = $this->manageableBranches($user);
        $selectedBranch = $this->selectedBranch($branches, $request->integer('branch_id'));
        $profile = $selectedBranch ? $this->profileForBranch($selectedBranch) : null;

        return view('medcare.settings.apotekProfile.profile', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'profile' => $profile,
            'dayLabels' => self::DAY_LABELS,
            'operationalHours' => $this->operationalHours($profile),
        ]);
    }

    public function update(UpdateApotekProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($this->userCanManage($user), 403);

        $branches = $this->manageableBranches($user);
        $branch = $branches->firstWhere('id', $request->integer('branch_id'));
        abort_unless($branch, 403, 'Anda tidak memiliki akses ke cabang tersebut.');

        $profile = ApotekProfile::firstOrNew(['branch_id' => $branch->id]);
        $oldLogoPath = $profile->logo_path;
        $oldSignaturePath = $profile->pharmacist_signature_path;
        $newLogoPath = null;
        $newSignaturePath = null;

        $data = Arr::except($request->validated(), [
            'logo', 'remove_logo', 'pharmacist_signature', 'remove_pharmacist_signature',
        ]);
        $data['branch_id'] = $branch->id;
        $data['operational_hours'] = $this->normalizeOperationalHours($request->input('operational_hours', []));

        try {
            if ($request->hasFile('logo')) {
                $newLogoPath = $request->file('logo')->store('apotek-logos', 'public');
                throw_unless($newLogoPath, \RuntimeException::class, 'Logo apotek gagal disimpan.');
                $data['logo_path'] = $newLogoPath;
            } elseif ($request->boolean('remove_logo')) {
                $data['logo_path'] = null;
            }

            if ($request->hasFile('pharmacist_signature')) {
                $newSignaturePath = $request->file('pharmacist_signature')->store('apotek-signatures', 'public');
                throw_unless($newSignaturePath, \RuntimeException::class, 'Tanda tangan apoteker gagal disimpan.');
                $data['pharmacist_signature_path'] = $newSignaturePath;
            } elseif ($request->boolean('remove_pharmacist_signature')) {
                $data['pharmacist_signature_path'] = null;
            }

            $profile->fill($data)->save();
        } catch (\Throwable $exception) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }
            if ($newSignaturePath) {
                Storage::disk('public')->delete($newSignaturePath);
            }

            throw $exception;
        }

        if ($oldLogoPath && ($newLogoPath || $request->boolean('remove_logo'))) {
            Storage::disk('public')->delete($oldLogoPath);
        }
        if ($oldSignaturePath && ($newSignaturePath || $request->boolean('remove_pharmacist_signature'))) {
            Storage::disk('public')->delete($oldSignaturePath);
        }

        return redirect()
            ->route('settings.apotek-profile.index', ['branch_id' => $branch->id])
            ->with('success', "Profile {$profile->name} berhasil disimpan.");
    }

    private function userCanManage(?User $user): bool
    {
        return (bool) $user && (
            $user->hasAnyRole(self::MANAGER_ROLES)
            || $user->can('MEDCARE.SETTINGS.PROFILE_APOTEK')
        );
    }

    private function manageableBranches(User $user): Collection
    {
        $query = BranchModel::query()->orderByDesc('is_active')->orderBy('name');

        if ($user->hasAnyRole(['Admin', 'admin']) || $user->can('MEDCARE.SETTINGS.PROFILE_APOTEK')) {
            return $query->get();
        }

        return $query->whereIn('id', BranchAccess::userBranchIds($user))->get();
    }

    private function selectedBranch(Collection $branches, int $requestedBranchId): ?BranchModel
    {
        if ($requestedBranchId) {
            return $branches->firstWhere('id', $requestedBranchId) ?: $branches->first();
        }

        return $branches->first();
    }

    private function profileForBranch(BranchModel $branch): ApotekProfile
    {
        $profile = ApotekProfile::firstOrNew(['branch_id' => $branch->id]);

        if (! $profile->exists) {
            $profile->fill([
                'name' => $branch->name,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'email' => $branch->email,
            ]);
        }

        return $profile;
    }

    private function operationalHours(?ApotekProfile $profile): array
    {
        $savedHours = $profile?->operational_hours ?? [];

        return collect(self::DAY_LABELS)
            ->mapWithKeys(function ($label, $day) use ($savedHours) {
                $defaults = [
                    'enabled' => $day !== 'sunday',
                    'open' => '08:00',
                    'close' => '21:00',
                ];

                return [$day => array_merge($defaults, $savedHours[$day] ?? [])];
            })
            ->all();
    }

    private function normalizeOperationalHours(array $hours): array
    {
        return collect(self::DAY_LABELS)
            ->mapWithKeys(fn ($label, $day) => [
                $day => [
                    'enabled' => filter_var(Arr::get($hours, "$day.enabled", false), FILTER_VALIDATE_BOOL),
                    'open' => Arr::get($hours, "$day.open", '08:00'),
                    'close' => Arr::get($hours, "$day.close", '21:00'),
                ],
            ])
            ->all();
    }
}
