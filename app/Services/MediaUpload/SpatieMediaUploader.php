<?php

namespace App\Services\MediaUpload;

use App\Services\MediaUpload\Contracts\MediaUploaderContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Wraps Spatie Media Library to persist a (possibly processed) file onto a model.
 */
final class SpatieMediaUploader implements MediaUploaderContract
{
    public function __construct(
        private readonly UploadedFile $file,
        private readonly UploadOptions $options,
    ) {}

    public function uploadTo(Model $model, string $collection = 'default'): Media
    {
        $this->assertModelHasMedia($model);

        $adder = $model
            ->addMedia($this->file)
            ->usingName($this->options->customName ?? pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME))
            ->usingFileName($this->buildFileName());

        if ($this->options->customProperties !== []) {
            $adder->withCustomProperties($this->options->customProperties);
        }

        return $adder->toMediaCollection($this->options->collection);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function buildFileName(): string
    {
        $name      = $this->options->customName
            ?? pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $this->file->getClientOriginalExtension() ?: 'jpg';

        return $name . '_' . now()->timestamp . '.' . $extension;
    }

    private function assertModelHasMedia(Model $model): void
    {
        if (! ($model instanceof HasMedia)) {
            throw new \InvalidArgumentException(
                sprintf('Model [%s] must implement HasMedia to use MediaUploadService.', $model::class)
            );
        }
    }
}
