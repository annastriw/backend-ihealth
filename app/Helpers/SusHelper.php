<?php

namespace App\Helpers;

class SusHelper
{
    public static function interpret(float $score): string
    {
        return match (true) {
            $score >= 85 => 'Excellent usability',
            $score >= 70 => 'Good usability',
            $score >= 50 => 'Acceptable usability',
            default => 'Poor usability'
        };
    }
}
