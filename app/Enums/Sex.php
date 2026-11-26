<?php

namespace App\Enums;

/**
 * Biological sex as reported in health records.
 */
enum Sex: string
{
    case Female = 'F';
    case Male = 'M';
    case Undetermined = 'I';

    public function label(): string
    {
        return match ($this) {
            self::Female => 'Femenino',
            self::Male => 'Masculino',
            self::Undetermined => 'Indeterminado',
        };
    }
}
