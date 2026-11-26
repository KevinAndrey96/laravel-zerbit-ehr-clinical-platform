<?php

namespace App\Enums;

/**
 * How a lab result reaches the patient.
 */
enum DeliveryChannel: string
{
    case Portal = 'portal';
    case InPerson = 'presencial';

    public function label(): string
    {
        return match ($this) {
            self::Portal => 'Portal del paciente',
            self::InPerson => 'Presencial',
        };
    }
}
