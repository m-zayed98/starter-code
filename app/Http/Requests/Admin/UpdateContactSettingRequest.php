<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facebook_link' => ['nullable', 'url'],
            'x_link' => ['nullable', 'url'],
            'instagram_link' => ['nullable', 'url'],
            'snapchat_link' => ['nullable', 'url'],
            'tiktok_link' => ['nullable', 'url'],
            'youtube_link' => ['nullable', 'url'],
            'whatsapp_number' => ['nullable', 'regex:/^05[0-9]{8}$/'],
            'contact_numbers' => ['required', 'array'],
            'contact_numbers.*' => ['required', 'regex:/^05[0-9]{8}$/'],
        ];
    }
}
