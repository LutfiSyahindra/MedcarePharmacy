<?php

namespace App\Support;

use App\Models\MarginsModel;

class MarginPriceHistory
{
    public static function marginLabel(?MarginsModel $margin): string
    {
        if (! $margin) {
            return '(referensi tidak tersedia)';
        }

        $level = match ($margin->tingkat) {
            'kategori' => 'Kategori',
            'golongan' => 'Golongan',
            'main_golongan' => 'Main Golongan',
            'sub_golongan' => 'Sub Golongan',
            'obat' => 'Obat',
            default => 'Referensi',
        };
        $reference = $margin->getReference();
        $name = $reference?->nama ?? $reference?->name ?? $reference?->nama_obat;

        return $name ? $level.' "'.$name.'"' : $level.' (referensi tidak tersedia)';
    }

    public static function describe(
        string $marginLabel,
        string $productName,
        ?string $batchNumber,
        float $oldPrice,
        float $newPrice,
        ?float $oldFactor = null,
        ?float $newFactor = null
    ): string {
        $reason = 'Perubahan margin '.$marginLabel;

        if ($oldFactor !== null && $newFactor !== null) {
            $reason .= ': '.self::factorLabel($oldFactor).' → '.self::factorLabel($newFactor);
        } elseif ($newFactor !== null) {
            $reason .= ': margin baru '.self::factorLabel($newFactor);
        }

        $batch = $batchNumber ? 'batch "'.$batchNumber.'"' : 'batch tidak lagi tersedia';

        return $reason.'. Harga jual "'.$productName.'" ('.$batch.'): Rp'
            .number_format($oldPrice, 2, ',', '.').' → Rp'.number_format($newPrice, 2, ',', '.').'.';
    }

    private static function factorLabel(float $factor): string
    {
        $percent = rtrim(rtrim(number_format(($factor - 1) * 100, 2, ',', '.'), '0'), ',');
        $precision = abs($factor - round($factor, 2)) > 0.0000001 ? 3 : 2;

        return $percent.'% (faktor jual '.number_format($factor, $precision, ',', '.').')';
    }
}
