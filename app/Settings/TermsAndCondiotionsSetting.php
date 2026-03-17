<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TermsAndCondiotionsSetting extends Settings
{
    public ?string $value_ar;
    public ?string $value_en;

    public static function group(): string
    {
        return 'terms_and_conditions';
    }
}

