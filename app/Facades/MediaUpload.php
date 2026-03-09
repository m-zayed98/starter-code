<?php

namespace App\Facades;

use App\Services\MediaUpload\MediaUploadService;
use Illuminate\Support\Facades\Facade;

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  MediaUpload  –  Laravel Facade                                         │
 * │                                                                         │
 * │  Provides a static proxy to MediaUploadService.                        │
 * │  Each static call resolves a FRESH instance from the container,         │
 * │  so fluent chains never bleed state between calls.                      │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * Usage:
 *
 *   use App\Facades\MediaUpload;
 *
 *   $media = MediaUpload::file($request->file('photo'))
 *       ->quality(75)
 *       ->resize(1200, 800)
 *       ->collection('photos')
 *       ->uploadTo($post);
 *
 * @method static \App\Services\MediaUpload\MediaUploadService file(\Illuminate\Http\UploadedFile $file)
 * @method static \App\Services\MediaUpload\MediaUploadService quality(int $quality)
 * @method static \App\Services\MediaUpload\MediaUploadService resize(int $width, ?int $height = null, bool $maintainAspect = true)
 * @method static \App\Services\MediaUpload\MediaUploadService fit(int $width, int $height)
 * @method static \App\Services\MediaUpload\MediaUploadService collection(string $collection)
 * @method static \App\Services\MediaUpload\MediaUploadService name(string $name)
 * @method static \App\Services\MediaUpload\MediaUploadService properties(array $properties)
 * @method static \Spatie\MediaLibrary\MediaCollections\Models\Media uploadTo(\Illuminate\Database\Eloquent\Model $model)
 *
 * @see \App\Services\MediaUpload\MediaUploadService
 */
final class MediaUpload extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MediaUploadService::class;
    }

    /**
     * Override: always resolve a FRESH instance so builder chains are isolated.
     */
    protected static function resolveFacadeInstance($name): MediaUploadService
    {
        // Never cache – every static entry-point gets a pristine service.
        return static::$app->make($name);
    }
}
