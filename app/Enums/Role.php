<?php

namespace App\Enums;

/**
 * Who works in the clinic.
 */
enum Role: string
{
    /** Front desk: staff, patients, schedule and laboratory. Never reads clinical notes. */
    case Admin = 'administrador';

    /** Opens clinical histories and writes session notes. */
    case Therapist = 'fisioterapeuta';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrativo',
            self::Therapist => 'Fisioterapeuta',
        };
    }
}
