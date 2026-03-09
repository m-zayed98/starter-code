<?php

namespace App\Services\MediaUpload\Contracts;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaUploaderContract
{
    public function uploadTo(Model $model, string $collection = 'default'): Media;
}
