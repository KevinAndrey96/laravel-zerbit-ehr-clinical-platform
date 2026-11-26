<?php

namespace App\Support;

use DateTimeInterface;

/**
 * Colombian number and date formatting for views, mails and PDF reports.
 */
final class Format
{
    private const array MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    private const array WEEKDAYS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

    public function number(float|int|string|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    public function percent(?float $ratio): string
    {
        return $ratio === null ? 'N/D' : $this->number($ratio * 100, 0).' %';
    }

    public function cop(?int $value): string
    {
        return $value === null ? 'Sin cobro' : '$ '.$this->number($value);
    }

    public function date(?DateTimeInterface $date): string
    {
        if ($date === null) {
            return '';
        }

        return $date->format('j').' '.self::MONTHS[(int) $date->format('n') - 1].' '.$date->format('Y');
    }

    public function longDate(DateTimeInterface $date): string
    {
        return self::WEEKDAYS[(int) $date->format('w')].' '.$this->date($date);
    }

    public function time(DateTimeInterface $date): string
    {
        return $date->format('g:i').' '.($date->format('A') === 'AM' ? 'a. m.' : 'p. m.');
    }

    public function dateTime(?DateTimeInterface $date): string
    {
        return $date === null ? '' : $this->date($date).', '.$this->time($date);
    }
}
