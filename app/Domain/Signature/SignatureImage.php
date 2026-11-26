<?php

namespace App\Domain\Signature;

use InvalidArgumentException;

/**
 * Handwritten signature captured on a canvas and sent as a PNG data URL.
 * Only real, reasonably small PNG images get through.
 */
final readonly class SignatureImage
{
    public const int MAX_BYTES = 200_000;

    private const string PREFIX = 'data:image/png;base64,';

    private function __construct(public string $png) {}

    public static function fromDataUrl(string $dataUrl): self
    {
        if (! str_starts_with($dataUrl, self::PREFIX)) {
            throw new InvalidArgumentException('signature must be a png data url');
        }
        $binary = base64_decode(substr($dataUrl, strlen(self::PREFIX)), true);
        if ($binary === false || $binary === '') {
            throw new InvalidArgumentException('signature is not valid base64');
        }
        if (strlen($binary) > self::MAX_BYTES) {
            throw new InvalidArgumentException('signature image is too large');
        }
        $info = @getimagesizefromstring($binary);
        if ($info === false || $info[2] !== IMAGETYPE_PNG) {
            throw new InvalidArgumentException('signature is not a png image');
        }
        if ($info[0] < 100 || $info[1] < 40 || $info[0] > 2000 || $info[1] > 1000) {
            throw new InvalidArgumentException('signature has unexpected dimensions');
        }

        return new self($binary);
    }

    public function dataUrl(): string
    {
        return self::PREFIX.base64_encode($this->png);
    }
}
