<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.is_free_period_enabled', false);
        $this->migrator->add('general.free_period_start_date', null);
        $this->migrator->add('general.free_period_end_date', null);
        $this->migrator->add('general.free_period_reason_ar', null);
        $this->migrator->add('general.free_period_reason_en', null);
    }

    public function down(): void
    {
        $this->migrator->delete('general.is_free_period_enabled');
        $this->migrator->delete('general.free_period_start_date');
        $this->migrator->delete('general.free_period_end_date');
        $this->migrator->delete('general.free_period_reason_ar');
        $this->migrator->delete('general.free_period_reason_en');
    }
};
