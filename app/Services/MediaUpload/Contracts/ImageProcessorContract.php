<?php

namespace App\Services\MediaUpload\Contracts;

use Illuminate\Http\UploadedFile;

interface ImageProcessorContract
{
    public function compress(UploadedFile $file, int $quality): static;

    public function resize(int $width, ?int $height = null, bool $maintainAspectRatio = true): static;

    public function fit(int $width, int $height): static;

    public function getProcessed(): UploadedFile;
}
