<?php

namespace App\Http\Requests\User;

use App\Enums\OtpPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserResendOtpRequest extends FormRequest
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
            'purpose' => ['required', 'string', Rule::in(array_map(fn (OtpPurpose $p) => $p->value, OtpPurpose::cases()))],
        ];
    }

    public function getLoginKeyAndValue(): array
    {
        $key = $this->filled('email') ? 'email' : 'phone';
        $value = $this->input($key);

        return [$key, $value];
    }
}
