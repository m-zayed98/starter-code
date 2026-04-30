<?php

namespace App\Http\Requests\User;

use App\Rules\ValidDialCode;
use App\Rules\ValidMobilePhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth('api')->user();

        return [
            'country_code' => ['required', new ValidDialCode(), 'bail'],
            'phone'        => [
                'required',
                'string',
                Rule::unique('users', 'phone')
                    ->where('country_code', $this->country_code)
                    ->ignore($user?->id),
                new ValidMobilePhone($this->country_code),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => ltrim($this->input('phone', ''), '0'),
        ]);
    }
}
