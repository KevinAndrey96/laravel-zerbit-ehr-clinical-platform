<?php

namespace App\Domain\Scheduling;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Half-open interval [start, end) on a therapist's agenda.
 */
final readonly class TimeSlot
{
    public function __construct(
        public CarbonImmutable $start,
        public CarbonImmutable $end,
    ) {
        if ($end <= $start) {
            throw new InvalidArgumentException('a slot must end after it starts');
        }
    }

    public static function lasting(CarbonImmutable $start, int $minutes): self
    {
        return new self($start, $start->addMinutes($minutes));
    }

    public function overlaps(self $other): bool
    {
        return $this->start < $other->end && $other->start < $this->end;
    }

    public function minutes(): int
    {
        return (int) $this->start->diffInMinutes($this->end);
    }
}
