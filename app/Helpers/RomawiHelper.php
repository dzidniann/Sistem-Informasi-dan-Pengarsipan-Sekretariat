<?php

namespace App\Helpers;

class RomawiHelper
{
    public static function dariBulan(int $bulan): string
    {
        $peta = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $peta[$bulan] ?? '';
    }
}