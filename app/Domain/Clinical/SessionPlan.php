<?php

namespace App\Domain\Clinical;

use InvalidArgumentException;

/**
 * Sessions authorized by the insurer for a treatment and how many were held.
 */
final readonly class SessionPlan
{
    public function __construct(
        public int $authorized,
        public int $held,
    ) {
        if ($authorized < 1) {
            throw new InvalidArgumentException('a plan needs at least one authorized session');
        }
        if ($held < 0) {
            throw new InvalidArgumentException('held sessions cannot be negative');
        }
    }

    public function remaining(): int
    {
        return max(0, $this->authorized - $this->held);
    }

    public function isExhausted(): bool
    {
        return $this->held >= $this->authorized;
    }

    public function nextSessionNumber(): int
    {
        if ($this->isExhausted()) {
            throw new InvalidArgumentException('all authorized sessions were held');
        }

        return $this->held + 1;
    }

    public function progress(): float
    {
        return min(1.0, $this->held / $this->authorized);
    }
}
