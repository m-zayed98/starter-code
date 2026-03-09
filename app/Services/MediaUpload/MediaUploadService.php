<?php

namespace App\Services\MediaUpload;

use App\Services\MediaUpload\Contracts\ImageProcessorContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  MediaUploadService  –  Util / Service class                            │
 * │                                                                         │
 * │  Orchestrates image processing (Intervention) + persistence (Spatie).   │
 * │  Fully injectable; zero static state.                                   │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * Usage (direct injection):
 *
 *   $media = app(MediaUploadService::class)
 *       ->file($request->file('avatar'))
 *       ->quality(75)
 *       ->resize(800, 600)
 *       ->collection('avatars')
 *       ->uploadTo($user);
 */
final class MediaUploadService
{
    private ?UploadedFile $file = null;

    private UploadOptions $options;

    public function __construct(
        private readonly ImageProcessorContract $processor,
    ) {
        $this->options = UploadOptions::defaults();
    }

    // ── Builder API ───────────────────────────────────────────────────────

    public function file(UploadedFile $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function quality(int $quality): static
    {
        $this->options = $this->options->withQuality($quality);

        return $this;
    }

    /**
     * Scale image to the given dimensions.
     * If $height is null and $maintainAspect is true, height is auto-calculated.
     */
    public function resize(int $width, ?int $height = null, bool $maintainAspect = true): static
    {
        $this->options = $this->options->withResize($width, $height, $maintainAspect);

        return $this;
    }

    /**
     * Crop / fit image to exact dimensions (no distortion).
     */
    public function fit(int $width, int $height): static
    {
        $this->options = $this->options->withFit($width, $height);

        return $this;
    }

    public function collection(string $collection): static
    {
        $this->options = $this->options->withCollection($collection);

        return $this;
    }

    public function name(string $name): static
    {
        $this->options = $this->options->withName($name);

        return $this;
    }

    public function properties(array $properties): static
    {
        $this->options = $this->options->withProperties($properties);

        return $this;
    }

    // ── Terminal action ───────────────────────────────────────────────────

    /**
     * Process the image (if needed) and attach it to the given Eloquent model.
     *
     * @throws \InvalidArgumentException when model does not implement HasMedia.
     * @throws \RuntimeException         when no file has been provided.
     */
    public function uploadTo(Model $model): Media
    {
        $file = $this->resolveFile();

        $uploader = new SpatieMediaUploader($file, $this->options);

        return $uploader->uploadTo($model, $this->options->collection);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * Routing logic:
     *
     *  1. Generic file (pdf, zip, doc …) → skip Intervention entirely, upload as-is.
     *  2. Keep-as-is image (gif, avif)   → skip resize/compress, upload as-is.
     *  3. WebP-convertible image         → run full Intervention pipeline → output as WebP.
     */
    private function resolveFile(): UploadedFile
    {
        if ($this->file === null) {
            throw new \RuntimeException('No file provided. Call file() before uploadTo().');
        }

        // ── Non-image file: bypass Intervention completely ────────────────
        if (MimeTypeDetector::isGenericFile($this->file)) {
            return $this->file;
        }

        // ── Keep-as-is image: no Intervention processing ──────────────────
        // (gif would lose animation; avif encoder support is inconsistent)
        if (MimeTypeDetector::isKeepAsIsImage($this->file)) {
            return $this->file;
        }

        // ── WebP-convertible image: run full pipeline ─────────────────────
        $this->processor->compress($this->file, $this->options->quality);

        if ($this->options->needsProcessing()) {
            if ($this->options->useFit) {
                $this->processor->fit(
                    $this->options->resizeWidth,
                    $this->options->resizeHeight,
                );
            } else {
                $this->processor->resize(
                    $this->options->resizeWidth,
                    $this->options->resizeHeight,
                    $this->options->maintainAspect,
                );
            }
        }

        // getProcessed() will encode to WebP automatically
        return $this->processor->getProcessed();
    }
}
