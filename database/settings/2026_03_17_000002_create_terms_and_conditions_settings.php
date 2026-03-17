<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('terms_and_conditions.value_ar', null);
        $this->migrator->add('terms_and_conditions.value_en', null);
    }

    public function down(): void
    {
        $this->migrator->delete('terms_and_conditions.value_ar');
        $this->migrator->delete('terms_and_conditions.value_en');
    }
};

