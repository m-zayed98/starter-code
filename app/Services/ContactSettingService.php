<?php

namespace App\Services;

use App\Http\Requests\Admin\UpdateContactSettingRequest;
use App\Settings\ContactSetting;

class ContactSettingService
{
    public function __construct(
        private ContactSetting $contactSetting
    ) {}

    public function getSettings(): array
    {
        return [
            'facebook_link' => $this->contactSetting->facebook_link,
            'x_link' => $this->contactSetting->x_link,
            'instagram_link' => $this->contactSetting->instagram_link,
            'snapchat_link' => $this->contactSetting->snapchat_link,
            'tiktok_link' => $this->contactSetting->tiktok_link,
            'youtube_link' => $this->contactSetting->youtube_link,
            'whatsapp_number' => $this->contactSetting->whatsapp_number,
            'contact_numbers' => $this->contactSetting->contact_numbers ?? [],
        ];
    }

    public function updateSettings(UpdateContactSettingRequest $request): void
    {
        $this->contactSetting->facebook_link = $request->input('facebook_link');
        $this->contactSetting->x_link = $request->input('x_link');
        $this->contactSetting->instagram_link = $request->input('instagram_link');
        $this->contactSetting->snapchat_link = $request->input('snapchat_link');
        $this->contactSetting->tiktok_link = $request->input('tiktok_link');
        $this->contactSetting->youtube_link = $request->input('youtube_link');
        $this->contactSetting->whatsapp_number = $request->input('whatsapp_number');
        $this->contactSetting->contact_numbers = $request->input('contact_numbers', []);

        $this->contactSetting->save();
    }
}
