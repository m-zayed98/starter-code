<?php

namespace App\Services;

use App\Http\Requests\Admin\UpdatePrivacySettingRequest;
use App\Settings\PrivacySetting;

class PrivacySettingService
{
    public function __construct(
        private PrivacySetting $privacySetting
    ) {}

    public function getSettings(): array
    {
        return [
            'value_ar' => $this->privacySetting->value_ar,
            'value_en' => $this->privacySetting->value_en,
        ];
    }

    public function getLocalizedSettings(): array
    {
        $locale = app()->getLocale();

        $primary = $locale === 'ar'
            ? $this->privacySetting->value_ar
            : $this->privacySetting->value_en;

        $fallback = $locale === 'ar'
            ? $this->privacySetting->value_en
            : $this->privacySetting->value_ar;

        return [
            'value' => $primary ?? $fallback,
        ];
    }

    public function updateSettings(UpdatePrivacySettingRequest $request): void
    {
        $this->privacySetting->value_ar = $request->input('value_ar');
        $this->privacySetting->value_en = $request->input('value_en');

        $this->privacySetting->save();
    }
}
