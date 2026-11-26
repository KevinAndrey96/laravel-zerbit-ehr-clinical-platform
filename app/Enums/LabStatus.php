<?php

namespace App\Enums;

/**
 * Lab sample workflow: registered at the front desk, analysed, result
 * uploaded and finally delivered to the patient.
 */
enum LabStatus: string
{
    case Registered = 'registrada';
    case InAnalysis = 'en_analisis';
    case ResultReady = 'resultado_listo';
    case Delivered = 'entregado';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registrada',
            self::InAnalysis => 'En análisis',
            self::ResultReady => 'Resultado listo',
            self::Delivered => 'Entregado',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Registered => 'slate',
            self::InAnalysis => 'amber',
            self::ResultReady => 'sky',
            self::Delivered => 'emerald',
        };
    }

    public function hasResult(): bool
    {
        return $this === self::ResultReady || $this === self::Delivered;
    }

    /** @return list<self> */
    public static function pending(): array
    {
        return [self::Registered, self::InAnalysis, self::ResultReady];
    }
}
