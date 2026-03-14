<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSetting extends Settings
{
    public ?string $facebook_link;
    public ?string $x_link;
    public ?string $instagram_link;
    public ?string $snapchat_link;
    public ?string $tiktok_link;
    public ?string $youtube_link;
    public ?string $whatsapp_number;
    public array $contact_numbers;

    public static function group(): string
    {
        return 'contact';
    }
}
