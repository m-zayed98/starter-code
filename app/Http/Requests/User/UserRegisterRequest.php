<?php

namespace App\Http\Requests\User;

use App\Rules\ValidDialCode;
use App\Rules\ValidMobilePhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', new ValidDialCode(), 'bail'],
            'phone' => [
                'required',
                'string',
                Rule::unique('users')->where('country_code', $this->country_code),
                new ValidMobilePhone($this->country_code)
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            // 'birth_date' => ['nullable', 'date'],
            // 'identity_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => ltrim($this->input('phone', ''), '0'),
        ]);
    }
}
