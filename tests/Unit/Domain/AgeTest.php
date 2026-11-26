<?php

namespace Tests\Unit\Domain;

use App\Domain\Clinical\Age;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AgeTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string, bool}>
     */
    public static function ages(): iterable
    {
        yield 'adult' => ['1991-04-12', '2026-11-26', '35 años', false];
        yield 'day before birthday' => ['2008-11-27', '2026-11-26', '17 años', true];
        yield 'eighteenth birthday' => ['2008-11-26', '2026-11-26', '18 años', false];
        yield 'toddler in months' => ['2025-03-10', '2026-11-26', '20 meses', true];
        yield 'one month' => ['2026-10-20', '2026-11-26', '1 mes', true];
        yield 'newborn' => ['2026-11-20', '2026-11-26', '0 meses', true];
    }

    #[DataProvider('ages')]
    public function test_labels_age_like_a_clinical_record(string $birthdate, string $on, string $label, bool $minor): void
    {
        $age = Age::between(new DateTimeImmutable($birthdate), new DateTimeImmutable($on));

        $this->assertSame($label, $age->label());
        $this->assertSame($minor, $age->isMinor());
    }

    public function test_rejects_birthdates_in_the_future(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Age::between(new DateTimeImmutable('2027-01-01'), new DateTimeImmutable('2026-11-26'));
    }
}
