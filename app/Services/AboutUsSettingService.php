<?php

namespace App\Services;

use App\Http\Requests\Admin\UpdateAboutUsSettingRequest;
use App\Settings\AboutUsSetting;

class AboutUsSettingService
{
    public function __construct(
        private AboutUsSetting $aboutUsSetting
    ) {}

    public function getSettings(): array
    {
        return [
            'value_ar' => $this->aboutUsSetting->value_ar,
            'value_en' => $this->aboutUsSetting->value_en,
        ];
    }

    public function updateSettings(UpdateAboutUsSettingRequest $request): void
    {
        $this->aboutUsSetting->value_ar = $request->input('value_ar');
        $this->aboutUsSetting->value_en = $request->input('value_en');

        $this->aboutUsSetting->save();
    }
}
