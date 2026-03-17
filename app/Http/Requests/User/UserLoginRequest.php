<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Contracts\AuthLoginRequestContract;
use App\Rules\ValidDialCode;
use App\Rules\ValidMobilePhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserLoginRequest extends FormRequest implements AuthLoginRequestContract
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
            'email' => ['required_without:phone', 'nullable', 'string', 'email'],
            'country_code' => ['required_with:phone', 'nullable', new ValidDialCode(), 'bail'],
            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                Rule::when($this->filled('phone'), [new ValidMobilePhone($this->country_code)], []),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => ltrim($this->input('phone', ''), '0'),
        ]);
    }

    /**
     * The attribute used to authenticate the user (email or phone).
     */
    public function getAuthKey(): string
    {
        return $this->filled('email') ? 'email' : 'phone';
    }
}
