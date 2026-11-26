<?php

namespace Tests\Unit\Domain;

use App\Domain\Scheduling\TimeSlot;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TimeSlotTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int, bool}>
     */
    public static function candidates(): iterable
    {
        yield 'same start' => ['09:00', 45, true];
        yield 'starts inside' => ['09:30', 30, true];
        yield 'wraps around' => ['08:30', 90, true];
        yield 'ends exactly when the other starts' => ['08:15', 45, false];
        yield 'starts exactly when the other ends' => ['09:45', 30, false];
        yield 'later in the day' => ['14:00', 60, false];
    }

    #[DataProvider('candidates')]
    public function test_detects_overlaps_as_half_open_intervals(string $start, int $minutes, bool $overlaps): void
    {
        $booked = TimeSlot::lasting(CarbonImmutable::parse('2026-11-27 09:00'), 45);
        $candidate = TimeSlot::lasting(CarbonImmutable::parse('2026-11-27 '.$start), $minutes);

        $this->assertSame($overlaps, $booked->overlaps($candidate));
        $this->assertSame($overlaps, $candidate->overlaps($booked));
    }

    public function test_measures_its_length(): void
    {
        $this->assertSame(45, TimeSlot::lasting(CarbonImmutable::parse('2026-11-27 09:00'), 45)->minutes());
    }

    public function test_must_end_after_it_starts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TimeSlot::lasting(CarbonImmutable::parse('2026-11-27 09:00'), 0);
    }
}
