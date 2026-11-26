<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'programada';
    case Confirmed = 'confirmada';
    case Attended = 'atendida';
    case Cancelled = 'cancelada';
    case NoShow = 'inasistencia';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Programada',
            self::Confirmed => 'Confirmada',
            self::Attended => 'Atendida',
            self::Cancelled => 'Cancelada',
            self::NoShow => 'No asistió',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Scheduled => 'slate',
            self::Confirmed => 'sky',
            self::Attended => 'emerald',
            self::Cancelled => 'rose',
            self::NoShow => 'amber',
        };
    }

    /** Scheduled and confirmed appointments still hold the therapist's time. */
    public function holdsSlot(): bool
    {
        return $this === self::Scheduled || $this === self::Confirmed;
    }

    /** @return list<self> */
    public function transitions(): array
    {
        return match ($this) {
            self::Scheduled => [self::Confirmed, self::Attended, self::Cancelled, self::NoShow],
            self::Confirmed => [self::Attended, self::Cancelled, self::NoShow],
            self::Attended, self::Cancelled, self::NoShow => [],
        };
    }

    /** @return list<string> */
    public static function slotValues(): array
    {
        return [self::Scheduled->value, self::Confirmed->value];
    }
}
