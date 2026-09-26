<?php

namespace App\Support;

use App\Models\MasterObatModel;
use App\Models\SuratPesananSetting;
use Illuminate\Support\Facades\Schema;

class ControlledDrugClassification
{
    /** @var array<string, array<int, string>>|null */
    private static ?array $settings = null;

    private static ?bool $settingsTableExists = null;

    /**
     * Resolve exactly one SP type for a medicine. Explicit settings are checked
     * from the most specific level (Sub Golongan) to the broadest (Golongan).
     * Name/code inference remains as a safe fallback for unconfigured records.
     *
     * @return array{type: string, classification: string, level: string, source: string}
     */
    public static function resolve(?MasterObatModel $medicine): array
    {
        if (! $medicine) {
            return self::regularResult();
        }

        $candidates = self::classificationCandidates($medicine);
        $settings = collect($candidates)->contains(fn (array $candidate): bool => (bool) $candidate['model']?->getKey())
            ? self::settings()
            : ['golongan' => [], 'main_golongan' => [], 'sub_golongan' => []];

        foreach ($candidates as $candidate) {
            $id = $candidate['model']?->getKey();
            $type = $id ? ($settings[$candidate['key']][(int) $id] ?? null) : null;

            if ($type) {
                return [
                    'type' => $type,
                    'classification' => self::classificationLabel($candidate['label'], $candidate['model']),
                    'level' => $candidate['key'],
                    'source' => 'setting',
                ];
            }
        }

        foreach ($candidates as $candidate) {
            $type = self::inferredTypeForClassification($candidate['model']);

            if ($type) {
                return [
                    'type' => $type,
                    'classification' => self::classificationLabel($candidate['label'], $candidate['model']),
                    'level' => $candidate['key'],
                    'source' => 'automatic',
                ];
            }
        }

        return self::regularResult();
    }

    public static function typeFor(?MasterObatModel $medicine): string
    {
        return self::resolve($medicine)['type'];
    }

    public static function matchesType(?MasterObatModel $medicine, string $type): bool
    {
        return self::typeFor($medicine) === $type;
    }

    public static function matchedClassificationForType(?MasterObatModel $medicine, string $type): ?string
    {
        $resolved = self::resolve($medicine);

        return $resolved['type'] === $type ? $resolved['classification'] : null;
    }

    /**
     * Used by the settings screen to preview automatic fallback behavior.
     */
    public static function inferredTypeForClassification(?object $classification): ?string
    {
        if (! $classification) {
            return null;
        }

        $code = strtolower(trim((string) ($classification->kode ?? '')));
        $text = strtolower(implode(' ', array_filter([
            $classification->kode ?? null,
            $classification->nama ?? null,
            $classification->keterangan ?? null,
        ])));

        $definitions = [
            SuratPesananSetting::TYPE_NARCOTIC => [
                'needles' => ['narkot'],
                'codes' => ['nar', 'nark', 'narkotika'],
            ],
            SuratPesananSetting::TYPE_PSYCHOTROPIC => [
                'needles' => ['psikotrop'],
                'codes' => ['pis', 'psi', 'psk', 'psikotropika'],
            ],
            SuratPesananSetting::TYPE_PRECURSOR => [
                'needles' => ['prekur'],
                'codes' => ['pr', 'prk', 'pre', 'prekursor'],
            ],
            SuratPesananSetting::TYPE_OOT => [
                'needles' => ['oot', 'obat-obat tertentu', 'obat tertentu'],
                'codes' => ['ot', 'otk', 'oot'],
            ],
        ];

        foreach ($definitions as $type => $definition) {
            if (in_array($code, $definition['codes'], true)) {
                return $type;
            }

            foreach ($definition['needles'] as $needle) {
                if (str_contains($text, $needle)) {
                    return $type;
                }
            }
        }

        return null;
    }

    public static function flushSettingsCache(): void
    {
        self::$settings = null;
        self::$settingsTableExists = null;
    }

    /**
     * @return array<int, array{key: string, label: string, model: object|null}>
     */
    private static function classificationCandidates(MasterObatModel $medicine): array
    {
        $sub = $medicine->subGolongan;
        $main = $medicine->mainGolongan ?: $sub?->mainGolongan;
        $golongan = $medicine->golongan ?: $main?->golongan ?: $sub?->mainGolongan?->golongan;

        return [
            ['key' => 'sub_golongan', 'label' => 'Sub Golongan', 'model' => $sub],
            ['key' => 'main_golongan', 'label' => 'Main Golongan', 'model' => $main],
            ['key' => 'golongan', 'label' => 'Golongan', 'model' => $golongan],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function settings(): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }

        $result = [
            'golongan' => [],
            'main_golongan' => [],
            'sub_golongan' => [],
        ];

        if (! self::settingsTableExists()) {
            return self::$settings = $result;
        }

        foreach (SuratPesananSetting::query()->get() as $setting) {
            foreach (array_keys($result) as $level) {
                $foreignKey = SuratPesananSetting::foreignKeyForLevel($level);
                $id = $foreignKey ? $setting->{$foreignKey} : null;

                if ($id) {
                    $result[$level][(int) $id] = $setting->sp_type;
                    break;
                }
            }
        }

        return self::$settings = $result;
    }

    private static function settingsTableExists(): bool
    {
        return self::$settingsTableExists ??= Schema::hasTable('surat_pesanan_settings');
    }

    private static function classificationLabel(string $level, object $classification): string
    {
        $name = trim((string) ($classification->nama ?: $classification->kode));

        return $level.($name !== '' ? ': '.$name : '');
    }

    /** @return array{type: string, classification: string, level: string, source: string} */
    private static function regularResult(): array
    {
        return [
            'type' => SuratPesananSetting::TYPE_REGULAR,
            'classification' => 'Tidak memiliki klasifikasi SP khusus',
            'level' => 'none',
            'source' => 'automatic',
        ];
    }
}
