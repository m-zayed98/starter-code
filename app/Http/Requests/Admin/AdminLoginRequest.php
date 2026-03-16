<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Contracts\AuthLoginRequestContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AdminLoginRequest extends FormRequest implements AuthLoginRequestContract
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
            $this->getAuthKey() => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * The attribute used to authenticate the admin (e.g. email).
     */
    public function getAuthKey(): string
    {
        return 'email';
    }
}
