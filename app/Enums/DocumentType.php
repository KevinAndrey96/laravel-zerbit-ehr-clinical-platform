<?php

namespace App\Enums;

/**
 * Colombian identity documents accepted by health providers.
 */
enum DocumentType: string
{
    case CitizenCard = 'CC';
    case IdentityCard = 'TI';
    case ForeignerCard = 'CE';
    case Passport = 'PA';
    case CivilRegistry = 'RC';
    case TemporaryPermit = 'PPT';

    public function label(): string
    {
        return match ($this) {
            self::CitizenCard => 'Cédula de ciudadanía',
            self::IdentityCard => 'Tarjeta de identidad',
            self::ForeignerCard => 'Cédula de extranjería',
            self::Passport => 'Pasaporte',
            self::CivilRegistry => 'Registro civil',
            self::TemporaryPermit => 'Permiso por protección temporal',
        };
    }
}
