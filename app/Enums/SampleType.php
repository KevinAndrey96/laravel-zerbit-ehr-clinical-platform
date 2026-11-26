<?php

namespace App\Enums;

enum SampleType: string
{
    case Blood = 'sangre';
    case Urine = 'orina';
    case Stool = 'heces';
    case Swab = 'secrecion';
    case Other = 'otra';

    public function label(): string
    {
        return match ($this) {
            self::Blood => 'Sangre',
            self::Urine => 'Orina',
            self::Stool => 'Materia fecal',
            self::Swab => 'Secreción o frotis',
            self::Other => 'Otra',
        };
    }
}
