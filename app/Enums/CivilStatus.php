<?php

namespace App\Enums;

enum CivilStatus: string
{
    case Single = 'soltero';
    case Married = 'casado';
    case Partnered = 'union_libre';
    case Separated = 'separado';
    case Widowed = 'viudo';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Soltero(a)',
            self::Married => 'Casado(a)',
            self::Partnered => 'Unión libre',
            self::Separated => 'Separado(a) o divorciado(a)',
            self::Widowed => 'Viudo(a)',
        };
    }
}
