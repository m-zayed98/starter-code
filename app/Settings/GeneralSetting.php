<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSetting extends Settings
{
    public bool $is_free_period_enabled;
    public ?string $free_period_start_date;
    public ?string $free_period_end_date;
    public ?string $free_period_reason_ar;
    public ?string $free_period_reason_en;

    public static function group(): string
    {
        return 'general';
    }
}