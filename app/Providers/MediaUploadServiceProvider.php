<?php

namespace App\Providers;

use App\Services\MediaUpload\Contracts\ImageProcessorContract;
use App\Services\MediaUpload\InterventionImageProcessor;
use App\Services\MediaUpload\MediaUploadService;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class MediaUploadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── ImageManager (v3)  for Intervention Image V3 ─────────────────────────────────────────────
        $this->app->singleton(ImageManager::class, function () {
            $driver = extension_loaded('imagick') ? ImagickDriver::class : GdDriver::class;

            return new ImageManager(new $driver());
        });

        // ── Processor ─────────────────────────────────────────────────────
        $this->app->bind(ImageProcessorContract::class, function ($app) {
            return new InterventionImageProcessor(
                manager: $app->make(ImageManager::class),
            );
        });

        // ── Service (not singleton — fresh instance per resolution) ───────
        $this->app->bind(MediaUploadService::class, function ($app) {
            return new MediaUploadService(
                processor: $app->make(ImageProcessorContract::class),
            );
        });
    }

    public function boot(): void {}
}
