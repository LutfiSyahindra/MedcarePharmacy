<?php

namespace App\Support;

use App\Models\MasterObatModel;

class ControlledDrugClassification
{
    /**
     * @param  array<int, string>  $needles
     * @param  array<int, string>  $codes
     */
    public static function matchedClassification(
        ?MasterObatModel $medicine,
        array $needles,
        array $codes = []
    ): ?string {
        if (! $medicine) {
            return null;
        }

        $candidates = [
            ['Golongan', $medicine->golongan],
            ['Main Golongan', $medicine->mainGolongan],
            ['Golongan induk Main Golongan', $medicine->mainGolongan?->golongan],
            ['Sub Golongan', $medicine->subGolongan],
            ['Main Golongan induk Sub Golongan', $medicine->subGolongan?->mainGolongan],
            ['Golongan induk Sub Golongan', $medicine->subGolongan?->mainGolongan?->golongan],
        ];

        $normalizedCodes = array_map(
            fn (string $code) => strtolower(trim($code)),
            $codes
        );

        foreach ($candidates as [$level, $classification]) {
            if (! $classification || ! self::classificationMatches($classification, $needles, $normalizedCodes)) {
                continue;
            }

            $name = trim((string) ($classification->nama ?: $classification->kode));

            return $level.($name !== '' ? ': '.$name : '');
        }

        return null;
    }

    /**
     * @param  array<int, string>  $needles
     * @param  array<int, string>  $codes
     */
    private static function classificationMatches(object $classification, array $needles, array $codes): bool
    {
        $code = strtolower(trim((string) ($classification->kode ?? '')));
        $text = strtolower(implode(' ', array_filter([
            $classification->kode ?? null,
            $classification->nama ?? null,
            $classification->keterangan ?? null,
        ])));

        if (in_array($code, $codes, true)) {
            return true;
        }

        foreach ($needles as $needle) {
            if (str_contains($text, strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
