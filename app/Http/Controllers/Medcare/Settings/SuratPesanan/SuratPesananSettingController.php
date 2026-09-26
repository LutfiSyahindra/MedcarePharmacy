<?php

namespace App\Http\Controllers\Medcare\Settings\SuratPesanan;

use App\Http\Controllers\Controller;
use App\Models\GolonganModel;
use App\Models\MainGolonganModel;
use App\Models\MasterObatModel;
use App\Models\SubGolonganModel;
use App\Models\SuratPesananSetting;
use App\Support\ControlledDrugClassification;
use App\Support\SidebarPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SuratPesananSettingController extends Controller
{
    private const LEVELS = [
        'golongan' => [
            'label' => 'Golongan',
            'model' => GolonganModel::class,
            'count_column' => 'golongan_id',
        ],
        'main_golongan' => [
            'label' => 'Main Golongan',
            'model' => MainGolonganModel::class,
            'count_column' => 'main_golongan_id',
        ],
        'sub_golongan' => [
            'label' => 'Sub Golongan',
            'model' => SubGolonganModel::class,
            'count_column' => 'sub_golongan_id',
        ],
    ];

    public function index(Request $request)
    {
        $this->authorizeAccess($request);

        $settings = SuratPesananSetting::query()->get();
        $settingMap = $this->settingMap($settings);
        $medicineCounts = collect(self::LEVELS)->mapWithKeys(
            fn (array $definition, string $level): array => [
                $level => MasterObatModel::query()
                    ->whereNotNull($definition['count_column'])
                    ->selectRaw($definition['count_column'].' as reference_id, COUNT(*) as aggregate')
                    ->groupBy($definition['count_column'])
                    ->pluck('aggregate', 'reference_id')
                    ->map(fn ($count) => (int) $count)
                    ->all(),
            ]
        )->all();

        $golongans = GolonganModel::query()
            ->with(['mainGolongan' => fn ($query) => $query
                ->orderBy('nama')
                ->with(['subGolongan' => fn ($subQuery) => $subQuery->orderBy('nama')])])
            ->orderBy('nama')
            ->get();

        $rows = collect();
        foreach ($golongans as $golongan) {
            $rows->push($this->makeRow(
                'golongan',
                $golongan,
                [$golongan],
                $settingMap,
                $medicineCounts,
                null,
            ));

            foreach ($golongan->mainGolongan as $mainGolongan) {
                $rows->push($this->makeRow(
                    'main_golongan',
                    $mainGolongan,
                    [$mainGolongan, $golongan],
                    $settingMap,
                    $medicineCounts,
                    $golongan->nama,
                    'golongan:'.$golongan->id,
                ));

                foreach ($mainGolongan->subGolongan as $subGolongan) {
                    $rows->push($this->makeRow(
                        'sub_golongan',
                        $subGolongan,
                        [$subGolongan, $mainGolongan, $golongan],
                        $settingMap,
                        $medicineCounts,
                        $golongan->nama.' / '.$mainGolongan->nama,
                        'main_golongan:'.$mainGolongan->id,
                    ));
                }
            }
        }

        $typeCounts = collect(SuratPesananSetting::TYPES)
            ->mapWithKeys(fn (string $type): array => [$type => $settings->where('sp_type', $type)->count()])
            ->all();

        return view('medcare.settings.suratPesanan.index', [
            'rows' => $rows,
            'typeLabels' => SuratPesananSetting::TYPE_LABELS,
            'typeCounts' => $typeCounts,
            'classificationCount' => $rows->count(),
            'configuredCount' => $settings->count(),
            'medicineCount' => MasterObatModel::query()->count(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorizeAccess($request);

        $validated = $request->validate([
            'assignments' => ['required', 'array', 'max:5000'],
            'assignments.*.level' => ['required', Rule::in(array_keys(self::LEVELS))],
            'assignments.*.id' => ['required', 'integer', 'min:1'],
            'assignments.*.sp_type' => ['nullable', Rule::in(SuratPesananSetting::TYPES)],
        ]);

        $assignments = collect($validated['assignments']);
        $duplicate = $assignments
            ->map(fn (array $assignment): string => $assignment['level'].':'.$assignment['id'])
            ->duplicates()
            ->first();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'assignments' => 'Terdapat klasifikasi yang dikirim lebih dari satu kali.',
            ]);
        }

        $idsByLevel = $assignments
            ->groupBy('level')
            ->map(fn ($items) => $items->pluck('id')->map(fn ($id) => (int) $id)->unique()->values());

        foreach (self::LEVELS as $level => $definition) {
            $requestedIds = $idsByLevel->get($level, collect());
            if ($requestedIds->isEmpty()) {
                continue;
            }

            $foundIds = $definition['model']::query()->whereKey($requestedIds)->pluck('id');
            if ($foundIds->count() !== $requestedIds->count()) {
                throw ValidationException::withMessages([
                    'assignments' => 'Salah satu '.$definition['label'].' sudah tidak tersedia. Muat ulang halaman lalu coba lagi.',
                ]);
            }
        }

        DB::transaction(function () use ($assignments, $request): void {
            foreach ($assignments as $assignment) {
                $foreignKey = SuratPesananSetting::foreignKeyForLevel($assignment['level']);
                $target = [$foreignKey => (int) $assignment['id']];
                $type = $assignment['sp_type'] ?? null;

                if (! $type) {
                    SuratPesananSetting::query()->where($target)->delete();

                    continue;
                }

                SuratPesananSetting::query()->updateOrCreate($target, [
                    'sp_type' => $type,
                    'updated_by' => $request->user()?->id,
                ]);
            }
        });

        ControlledDrugClassification::flushSettingsCache();
        $settings = SuratPesananSetting::query()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Setting Surat Pesanan berhasil disimpan.',
            'summary' => [
                'configured' => $settings->count(),
                'types' => collect(SuratPesananSetting::TYPES)
                    ->mapWithKeys(fn (string $type): array => [$type => $settings->where('sp_type', $type)->count()])
                    ->all(),
            ],
        ]);
    }

    /**
     * @param  array<int, object>  $candidates
     * @param  array<string, array<int, string>>  $settingMap
     * @param  array<string, array<int, int>>  $medicineCounts
     * @return array<string, mixed>
     */
    private function makeRow(
        string $level,
        object $classification,
        array $candidates,
        array $settingMap,
        array $medicineCounts,
        ?string $parentPath = null,
        ?string $parentKey = null,
    ): array {
        $configuredType = $settingMap[$level][(int) $classification->id] ?? null;
        $resolvedType = null;
        $source = 'Deteksi otomatis';

        foreach ($candidates as $index => $candidate) {
            $candidateLevel = array_keys(self::LEVELS)[array_search($candidate::class, array_column(self::LEVELS, 'model'), true)];
            $type = $settingMap[$candidateLevel][(int) $candidate->id] ?? null;

            if ($type) {
                $resolvedType = $type;
                $source = $index === 0
                    ? 'Diatur langsung'
                    : 'Mengikuti '.self::LEVELS[$candidateLevel]['label'];
                break;
            }
        }

        $automaticType = null;
        foreach ($candidates as $candidate) {
            $automaticType = ControlledDrugClassification::inferredTypeForClassification($candidate);
            if ($automaticType) {
                break;
            }
        }
        $automaticType ??= SuratPesananSetting::TYPE_REGULAR;

        if (! $resolvedType) {
            foreach ($candidates as $candidate) {
                $resolvedType = ControlledDrugClassification::inferredTypeForClassification($candidate);
                if ($resolvedType) {
                    break;
                }
            }
        }

        $resolvedType ??= SuratPesananSetting::TYPE_REGULAR;

        return [
            'level' => $level,
            'level_label' => self::LEVELS[$level]['label'],
            'depth' => array_search($level, array_keys(self::LEVELS), true),
            'id' => (int) $classification->id,
            'kode' => (string) $classification->kode,
            'nama' => (string) $classification->nama,
            'parent_path' => $parentPath,
            'parent_key' => $parentKey,
            'medicine_count' => $medicineCounts[$level][(int) $classification->id] ?? 0,
            'configured_type' => $configuredType,
            'effective_type' => $resolvedType,
            'automatic_type' => $automaticType,
            'source' => $source,
        ];
    }

    /** @return array<string, array<int, string>> */
    private function settingMap($settings): array
    {
        $map = collect(self::LEVELS)->mapWithKeys(fn ($definition, $level) => [$level => []])->all();

        foreach ($settings as $setting) {
            foreach (array_keys(self::LEVELS) as $level) {
                $foreignKey = SuratPesananSetting::foreignKeyForLevel($level);
                if ($setting->{$foreignKey}) {
                    $map[$level][(int) $setting->{$foreignKey}] = $setting->sp_type;
                    break;
                }
            }
        }

        return $map;
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()?->can(SidebarPermissions::SETTINGS_SURAT_PESANAN), 403);
    }
}
