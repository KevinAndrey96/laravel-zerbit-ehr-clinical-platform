<?php

namespace Tests\Unit\Domain;

use App\Domain\Clinical\SessionPlan;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SessionPlanTest extends TestCase
{
    /**
     * @return iterable<string, array{int, int, int, bool, float}>
     */
    public static function plans(): iterable
    {
        yield 'not started' => [10, 0, 10, false, 0.0];
        yield 'half way' => [10, 5, 5, false, 0.5];
        yield 'last session left' => [10, 9, 1, false, 0.9];
        yield 'completed' => [10, 10, 0, true, 1.0];
        yield 'over the authorization' => [8, 9, 0, true, 1.0];
    }

    #[DataProvider('plans')]
    public function test_tracks_authorized_sessions(int $authorized, int $held, int $remaining, bool $exhausted, float $progress): void
    {
        $plan = new SessionPlan($authorized, $held);

        $this->assertSame($remaining, $plan->remaining());
        $this->assertSame($exhausted, $plan->isExhausted());
        $this->assertEqualsWithDelta($progress, $plan->progress(), 0.0001);
    }

    public function test_numbers_the_next_session(): void
    {
        $this->assertSame(1, (new SessionPlan(10, 0))->nextSessionNumber());
        $this->assertSame(10, (new SessionPlan(10, 9))->nextSessionNumber());
    }

    public function test_there_is_no_next_session_once_exhausted(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new SessionPlan(10, 10))->nextSessionNumber();
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function invalid(): iterable
    {
        yield 'no authorized sessions' => [0, 0];
        yield 'negative held' => [10, -1];
    }

    #[DataProvider('invalid')]
    public function test_rejects_impossible_plans(int $authorized, int $held): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SessionPlan($authorized, $held);
    }
}
