<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class InitiateNafathVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identity_number' => ['required', 'string', 'min:10', 'max:20'],
        ];
    }
}
