<?php

namespace Tests\Unit\Domain;

use App\Domain\Clinical\Cie10Code;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Cie10CodeTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function valid(): iterable
    {
        yield 'dotted' => ['M54.5', 'M54.5'];
        yield 'lowercase without dot' => ['m545', 'M54.5'];
        yield 'category only' => ['G56', 'G56'];
        yield 'two subcategory characters' => ['S83.51', 'S83.51'];
        yield 'surrounding spaces' => ['  m 17.1 ', 'M17.1'];
    }

    #[DataProvider('valid')]
    public function test_normalizes_to_the_dotted_form(string $input, string $expected): void
    {
        $this->assertSame($expected, (string) Cie10Code::parse($input));
    }

    public function test_exposes_the_chapter_letter(): void
    {
        $this->assertSame('M', Cie10Code::parse('M54.5')->chapter());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalid(): iterable
    {
        yield 'empty' => [''];
        yield 'starts with a digit' => ['545'];
        yield 'too short' => ['M5'];
        yield 'too long' => ['M54.512'];
        yield 'symbols' => ['M54-5'];
    }

    #[DataProvider('invalid')]
    public function test_rejects_malformed_codes(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        Cie10Code::parse($input);
    }
}
