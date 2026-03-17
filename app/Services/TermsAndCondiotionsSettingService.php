<?php

namespace App\Services;

use App\Http\Requests\Admin\UpdateTermsAndCondiotionsSettingRequest;
use App\Settings\TermsAndCondiotionsSetting;

class TermsAndCondiotionsSettingService
{
    public function __construct(
        private TermsAndCondiotionsSetting $termsSetting
    ) {}

    public function getSettings(): array
    {
        return [
            'value_ar' => $this->termsSetting->value_ar,
            'value_en' => $this->termsSetting->value_en,
        ];
    }

    public function updateSettings(UpdateTermsAndCondiotionsSettingRequest $request): void
    {
        $this->termsSetting->value_ar = $request->input('value_ar');
        $this->termsSetting->value_en = $request->input('value_en');

        $this->termsSetting->save();
    }
}
