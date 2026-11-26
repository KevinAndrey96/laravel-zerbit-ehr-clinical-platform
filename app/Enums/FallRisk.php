<?php

namespace App\Enums;

/**
 * Fall risk from the physiotherapy assessment.
 */
enum FallRisk: string
{
    case Low = 'bajo';
    case Moderate = 'moderado';
    case High = 'alto';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Bajo',
            self::Moderate => 'Moderado',
            self::High => 'Alto',
        };
    }
}
