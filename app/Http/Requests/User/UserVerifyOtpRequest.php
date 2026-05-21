<?php

namespace App\Http\Requests\User;

use App\Enums\OtpPurpose;
use App\Rules\ValidDialCode;
use App\Rules\ValidMobilePhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserVerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $purposesRequiringFcmToken = [
            OtpPurpose::REGISTER->value,
            OtpPurpose::LOGIN->value,
        ];

        $fcmTokenRequired = in_array($this->input('purpose'), $purposesRequiringFcmToken, true);

        return [
            'email' => ['required_without:phone', 'nullable', 'string', 'email'],
            'country_code' => ['required_with:phone', 'nullable', new ValidDialCode, 'bail'],
            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                Rule::when($this->filled('phone'), [new ValidMobilePhone($this->country_code)], []),
            ],
            'code' => ['required', 'string', 'size:6'],
            'purpose' => ['required', 'string'],
            'fcm_token' => [$fcmTokenRequired ? 'required' : 'nullable', 'string'],
            'device_type' => ['nullable', 'string', Rule::in(['ios', 'android', 'web'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => ltrim($this->input('phone', ''), '0'),
        ]);
    }

    public function getLoginKeyAndValue(): array
    {
        $key = $this->filled('email') ? 'email' : 'phone';
        $value = $this->input($key);
        $countryCode = $key === 'phone' ? $this->input('country_code') : null;

        return [$key, $value, $countryCode];
    }
}
