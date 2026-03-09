<?php

namespace App\Services\MediaUpload;

use App\Services\MediaUpload\Contracts\ImageProcessorContract;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;

/**
 * Intervention Image v3 adapter.
 *
 * v3 key differences from v2:
 *  - ImageManager::withDriver() / new ImageManager(driver: ...)  replaces Image::make()
 *  - ->read()           replaces ->make()
 *  - ->scale()          replaces ->resize() with aspect-ratio constraint
 *  - ->resize()         now forces exact dimensions (no aspect ratio)
 *  - ->cover()          replaces ->fit()
 *  - ->toJpeg(quality)  /  ->toPng()  etc. replace ->save($path, $quality)
 */
final class InterventionImageProcessor implements ImageProcessorContract
{
    private UploadedFile $file;

    private ?ImageInterface $image = null;

    private int $quality = 80;

    public function __construct(
        private readonly ImageManager $manager,
    ) {}

    // ── Contract implementation ───────────────────────────────────────────

    public function compress(UploadedFile $file, int $quality): static
    {
        $this->file    = $file;
        $this->quality = max(1, min(100, $quality));
        $this->image   = $this->manager->read($file->getRealPath());

        return $this;
    }

    /**
     * Scale image to the given width (and optional height).
     *
     * - $maintainAspectRatio = true  → uses v3 scale()  (proportional, no upscale)
     * - $maintainAspectRatio = false → uses v3 resize() (exact, may distort)
     */
    public function resize(int $width, ?int $height = null, bool $maintainAspectRatio = true): static
    {
        $this->ensureImageLoaded();

        if ($maintainAspectRatio) {
            // scale() keeps aspect ratio and never upscales beyond original size
            $this->image = $this->image->scale(
                width: $width,
                height: $height,
            );
        } else {
            // resize() forces exact dimensions
            $this->image = $this->image->resize(
                width: $width,
                height: $height ?? $this->image->height(),
            );
        }

        return $this;
    }

    /**
     * Crop and resize to exact dimensions from the center (no distortion).
     * Replaces v2's ->fit().
     */
    public function fit(int $width, int $height): static
    {
        $this->ensureImageLoaded();

        $this->image = $this->image->cover($width, $height);

        return $this;
    }

    /**
     * Encode and write to a temp file, return as a fresh UploadedFile.
     *
     * Strategy:
     *  - WebP-convertible types (jpg, png, bmp, tiff) → always saved as .webp
     *  - Keep-as-is types (gif, avif, webp)           → preserved in original format
     */
    public function getProcessed(): UploadedFile
    {
        $this->ensureImageLoaded();

        [$extension, $mimeType] = $this->resolveOutputFormat();

        $tmpPath = sys_get_temp_dir() . '/' . uniqid('media_', true) . '.' . $extension;

        $this->encodeAs($extension)->save($tmpPath);

        // Use the original filename but swap the extension to match the new format
        $originalBasename = pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);

        return new UploadedFile(
            path: $tmpPath,
            originalName: $originalBasename . '.' . $extension,
            mimeType: $mimeType,
            error: null,
            test: true,
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function ensureImageLoaded(): void
    {
        if ($this->image === null) {
            throw new RuntimeException(
                'Call compress() before any transformation method.'
            );
        }
    }

    /**
     * Decide the output [extension, mimeType] pair.
     *
     * WebP-convertible → ['webp', 'image/webp']
     * Keep-as-is       → original format
     */
    private function resolveOutputFormat(): array
    {
        if (MimeTypeDetector::isWebpConvertible($this->file)) {
            return ['webp', 'image/webp'];
        }

        // Keep-as-is image (gif, avif, webp already)
        return match (strtolower($this->file->getClientOriginalExtension())) {
            'gif'        => ['gif',  'image/gif'],
            'avif'       => ['avif', 'image/avif'],
            'webp'       => ['webp', 'image/webp'],
            default      => ['jpg',  'image/jpeg'],
        };
    }

    /**
     * Encode the processed image using the correct v3 typed encoder.
     */
    private function encodeAs(string $extension): \Intervention\Image\Interfaces\EncodedImageInterface
    {
        return match ($extension) {
            'webp'  => $this->image->toWebp(quality: $this->quality),
            'gif'   => $this->image->toGif(),
            'avif'  => $this->image->toAvif(quality: $this->quality),
            default => $this->image->toJpeg(quality: $this->quality),
        };
    }
}
