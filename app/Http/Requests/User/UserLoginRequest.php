<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Contracts\AuthLoginRequestContract;
use Illuminate\Foundation\Http\FormRequest;

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
            'phone' => ['required_without:email', 'nullable', 'string'],
        ];
    }

    /**
     * The attribute used to authenticate the user (email or phone).
     */
    public function getAuthKey(): string
    {
        return $this->filled('email') ? 'email' : 'phone';
    }
}
