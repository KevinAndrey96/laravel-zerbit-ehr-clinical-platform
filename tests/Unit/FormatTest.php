<?php

namespace Tests\Unit;

use App\Support\Format;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class FormatTest extends TestCase
{
    public function test_uses_colombian_separators(): void
    {
        $fmt = new Format;

        $this->assertSame('1.234.567', $fmt->number(1234567));
        $this->assertSame('12,50', $fmt->number(12.5, 2));
        $this->assertSame('$ 32.000', $fmt->cop(32000));
    }

    public function test_formats_percentages_and_missing_copayments(): void
    {
        $fmt = new Format;

        $this->assertSame('95 %', $fmt->percent(0.95));
        $this->assertSame('N/D', $fmt->percent(null));
        $this->assertSame('Sin cobro', $fmt->cop(null));
    }

    public function test_formats_times_in_colombian_style(): void
    {
        $fmt = new Format;
        $morning = new DateTimeImmutable('2026-03-05 08:05');
        $evening = new DateTimeImmutable('2026-03-07 15:30');

        $this->assertSame('8:05 a. m.', $fmt->time($morning));
        $this->assertSame('3:30 p. m.', $fmt->time($evening));
        $this->assertSame('5 mar 2026, 8:05 a. m.', $fmt->dateTime($morning));
        $this->assertSame('sábado 7 mar 2026', $fmt->longDate($evening));
    }

    public function test_formats_dates_in_spanish(): void
    {
        $this->assertSame('5 mar 2026', (new Format)->date(new DateTimeImmutable('2026-03-05')));
        $this->assertSame('', (new Format)->date(null));
    }
}
