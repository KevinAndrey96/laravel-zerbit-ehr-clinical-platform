<?php

namespace App\Domain\Clinical;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * Age at a given date, written the way clinical records show it: years for
 * adults and children, months for babies under two.
 */
final readonly class Age
{
    private function __construct(
        public int $years,
        public int $months,
    ) {}

    public static function between(DateTimeInterface $birthdate, DateTimeInterface $on): self
    {
        if ($birthdate > $on) {
            throw new InvalidArgumentException('birthdate is after the reference date');
        }
        $diff = $birthdate->diff($on);

        return new self($diff->y, $diff->y * 12 + $diff->m);
    }

    public function label(): string
    {
        if ($this->years >= 2) {
            return $this->years.' años';
        }

        return $this->months === 1 ? '1 mes' : $this->months.' meses';
    }

    public function isMinor(): bool
    {
        return $this->years < 18;
    }
}
