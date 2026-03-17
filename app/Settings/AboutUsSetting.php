<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AboutUsSetting extends Settings
{
    public ?string $value_ar;
    public ?string $value_en;

    public static function group(): string
    {
        return 'about_us';
    }
}

