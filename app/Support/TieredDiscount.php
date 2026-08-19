<?php

namespace App\Support;

final class TieredDiscount
{
    public static function normalize(mixed $value): float
    {
        return round(min(100, max(0, (float) ($value ?: 0))), 2);
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function percentages(mixed $discount1, mixed $discount2, mixed $discount3): array
    {
        return [
            self::normalize($discount1),
            self::normalize($discount2),
            self::normalize($discount3),
        ];
    }

    public static function netAmount(float $grossAmount, mixed $discount1, mixed $discount2, mixed $discount3): float
    {
        $netAmount = max(0, $grossAmount);

        foreach (self::percentages($discount1, $discount2, $discount3) as $discount) {
            $netAmount *= 1 - ($discount / 100);
        }

        return round(max(0, $netAmount), 2);
    }

    public static function discountAmount(float $grossAmount, mixed $discount1, mixed $discount2, mixed $discount3): float
    {
        $grossAmount = max(0, $grossAmount);

        return round($grossAmount - self::netAmount($grossAmount, $discount1, $discount2, $discount3), 2);
    }

    public static function effectivePercentage(mixed $discount1, mixed $discount2, mixed $discount3): float
    {
        $remainingFactor = 1.0;

        foreach (self::percentages($discount1, $discount2, $discount3) as $discount) {
            $remainingFactor *= 1 - ($discount / 100);
        }

        return round((1 - $remainingFactor) * 100, 2);
    }
}
