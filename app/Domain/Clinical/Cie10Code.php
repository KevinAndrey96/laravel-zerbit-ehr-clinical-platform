<?php

namespace App\Domain\Clinical;

use InvalidArgumentException;
use Stringable;

/**
 * ICD-10 (CIE-10) diagnosis code such as M54.5 or S83.2. Accepts the dot
 * or not, in any case, and stores the canonical dotted form.
 */
final readonly class Cie10Code implements Stringable
{
    private function __construct(public string $value) {}

    public static function parse(string $input): self
    {
        $compact = strtoupper(str_replace(['.', ' '], '', trim($input)));
        if (preg_match('/^[A-Z][0-9]{2}[0-9A-Z]{0,2}$/', $compact) !== 1) {
            throw new InvalidArgumentException('invalid cie-10 code');
        }

        return new self(strlen($compact) > 3 ? substr($compact, 0, 3).'.'.substr($compact, 3) : $compact);
    }

    public function chapter(): string
    {
        return $this->value[0];
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
