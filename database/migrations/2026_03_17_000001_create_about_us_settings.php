<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('about_us.value_ar', null);
        $this->migrator->add('about_us.value_en', null);
    }

    public function down(): void
    {
        $this->migrator->delete('about_us.value_ar');
        $this->migrator->delete('about_us.value_en');
    }
};

