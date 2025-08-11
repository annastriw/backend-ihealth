<?php

namespace App\Helpers;

class DASSHelper
{
    public static function interpret($score, $type)
    {
        if ($type === 'Depresi') {
            return match (true) {
                $score <= 4 => 'Normal',
                $score <= 6 => 'Ringan',
                $score <= 10 => 'Sedang',
                $score <= 13 => 'Parah',
                default => 'Sangat Parah',
            };
        }

        if ($type === 'Kecemasan') {
            return match (true) {
                $score <= 3 => 'Normal',
                $score <= 5 => 'Ringan',
                $score <= 7 => 'Sedang',
                $score <= 9 => 'Parah',
                default => 'Sangat Parah',
            };
        }

        if ($type === 'Stres') {
            return match (true) {
                $score <= 7 => 'Normal',
                $score <= 9 => 'Ringan',
                $score <= 12 => 'Sedang',
                $score <= 16 => 'Parah',
                default => 'Sangat Parah',
            };
        }

        return 'Normal';
    }

    public static function descriptions()
    {
        return json_decode(file_get_contents(__DIR__ . '/../../resources/dass_descriptions.json'), true);
    }
}
