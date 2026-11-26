<?php

namespace Tests\Unit\Domain;

use App\Domain\Signature\SignatureImage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SignatureImageTest extends TestCase
{
    /**
     * @param  positive-int  $width
     * @param  positive-int  $height
     */
    public static function png(int $width = 300, int $height = 100): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagepng($image);

        return 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
    }

    public function test_accepts_a_canvas_png(): void
    {
        $dataUrl = self::png();

        $signature = SignatureImage::fromDataUrl($dataUrl);

        $this->assertSame($dataUrl, $signature->dataUrl());
        $this->assertStringStartsWith("\x89PNG", $signature->png);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalid(): iterable
    {
        yield 'not a data url' => ['https://example.test/firma.png'];
        yield 'jpeg data url' => ['data:image/jpeg;base64,'.base64_encode('jpeg')];
        yield 'broken base64' => ['data:image/png;base64,***'];
        yield 'empty payload' => ['data:image/png;base64,'];
        yield 'text disguised as png' => ['data:image/png;base64,'.base64_encode('<?php echo 1;')];
        yield 'too small' => [self::png(50, 20)];
        yield 'too large' => [self::png(2500, 1200)];
    }

    #[DataProvider('invalid')]
    public function test_rejects_anything_that_is_not_a_reasonable_png(string $dataUrl): void
    {
        $this->expectException(InvalidArgumentException::class);
        SignatureImage::fromDataUrl($dataUrl);
    }
}
