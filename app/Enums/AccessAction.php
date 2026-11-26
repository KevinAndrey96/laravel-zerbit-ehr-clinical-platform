<?php

namespace App\Enums;

/**
 * Actions recorded in the clinical access log.
 */
enum AccessAction: string
{
    case Viewed = 'consulta';
    case Downloaded = 'pdf';
    case Opened = 'apertura';
    case Evolved = 'evolucion';
    case Signed = 'firma';
    case Discharged = 'alta';

    public function label(): string
    {
        return match ($this) {
            self::Viewed => 'Consultó la historia',
            self::Downloaded => 'Descargó el PDF',
            self::Opened => 'Abrió la historia',
            self::Evolved => 'Registró una sesión',
            self::Signed => 'Recogió una firma',
            self::Discharged => 'Dio de alta',
        };
    }
}
