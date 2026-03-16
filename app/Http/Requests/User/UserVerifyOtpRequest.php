<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserVerifyOtpRequest extends FormRequest
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
            'code' => ['required', 'string', 'size:6'],
            'purpose' => ['required', 'string'],
        ];
    }


    public function messages(): array
    {
        return [
            'email.required_without' => 'The email field is required.',
            'phone.required_without' => 'The phone field is required.',
        ];
    }
    public function getLoginKeyAndValue(): array
    {
        $key = $this->filled('email') ? 'email' : 'phone';
        $value = $this->input($key);

        return [$key, $value];
    }
}
