<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.facebook_link', null);
        $this->migrator->add('contact.x_link', null);
        $this->migrator->add('contact.instagram_link', null);
        $this->migrator->add('contact.snapchat_link', null);
        $this->migrator->add('contact.tiktok_link', null);
        $this->migrator->add('contact.youtube_link', null);
        $this->migrator->add('contact.whatsapp_number', null);
        $this->migrator->add('contact.contact_numbers', []);
    }

    public function down(): void
    {
        $this->migrator->delete('contact.facebook_link');
        $this->migrator->delete('contact.x_link');
        $this->migrator->delete('contact.instagram_link');
        $this->migrator->delete('contact.snapchat_link');
        $this->migrator->delete('contact.tiktok_link');
        $this->migrator->delete('contact.youtube_link');
        $this->migrator->delete('contact.whatsapp_number');
        $this->migrator->delete('contact.contact_numbers');
    }
};
