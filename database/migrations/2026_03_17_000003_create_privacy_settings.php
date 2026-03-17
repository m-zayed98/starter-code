<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('privacy.value_ar', null);
        $this->migrator->add('privacy.value_en', null);
    }

    public function down(): void
    {
        $this->migrator->delete('privacy.value_ar');
        $this->migrator->delete('privacy.value_en');
    }
};

