<?php

namespace App\Support;

class IndonesianNumber
{
    public static function spell(int $number): string
    {
        if ($number === 0) {
            return 'nol';
        }

        if ($number < 0) {
            return 'minus '.self::spell(abs($number));
        }

        return trim(self::spellPositiveInteger($number));
    }

    private static function spellPositiveInteger(int $number): string
    {
        $basic = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam',
            'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
        ];

        if ($number < 12) {
            return $basic[$number];
        }

        if ($number < 20) {
            return self::spellPositiveInteger($number - 10).' belas';
        }

        if ($number < 100) {
            return self::spellPositiveInteger(intdiv($number, 10)).' puluh '.self::spellPositiveInteger($number % 10);
        }

        if ($number < 200) {
            return 'seratus '.self::spellPositiveInteger($number - 100);
        }

        if ($number < 1000) {
            return self::spellPositiveInteger(intdiv($number, 100)).' ratus '.self::spellPositiveInteger($number % 100);
        }

        if ($number < 2000) {
            return 'seribu '.self::spellPositiveInteger($number - 1000);
        }

        if ($number < 1_000_000) {
            return self::spellPositiveInteger(intdiv($number, 1000)).' ribu '.self::spellPositiveInteger($number % 1000);
        }

        if ($number < 1_000_000_000) {
            return self::spellPositiveInteger(intdiv($number, 1_000_000)).' juta '.self::spellPositiveInteger($number % 1_000_000);
        }

        if ($number < 1_000_000_000_000) {
            return self::spellPositiveInteger(intdiv($number, 1_000_000_000)).' miliar '.self::spellPositiveInteger($number % 1_000_000_000);
        }

        return self::spellPositiveInteger(intdiv($number, 1_000_000_000_000)).' triliun '
            .self::spellPositiveInteger($number % 1_000_000_000_000);
    }
}
