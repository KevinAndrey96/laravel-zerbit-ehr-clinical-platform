<?php

namespace App\Support;

use App\Domain\Signature\SignatureImage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Signatures and lab results live on the private disk with random names;
 * they are never reachable through a public URL.
 */
final class PrivateFiles
{
    private const string DISK = 'local';

    public function storeSignature(SignatureImage $signature): string
    {
        $path = 'signatures/'.Str::uuid()->toString().'.png';
        if (! $this->disk()->put($path, $signature->png)) {
            throw new RuntimeException('signature storage');
        }

        return $path;
    }

    public function storeResult(UploadedFile $file): string
    {
        $path = $file->storeAs('lab-results', Str::uuid()->toString().'.pdf', self::DISK);
        if ($path === false) {
            throw new RuntimeException('lab result storage');
        }

        return $path;
    }

    public function dataUrl(?string $path): ?string
    {
        if ($path === null || ! $this->disk()->exists($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) $this->disk()->get($path));
    }

    public function delete(?string $path): void
    {
        if ($path !== null) {
            $this->disk()->delete($path);
        }
    }

    public function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }
}
