<?php

namespace App\Http\Requests\Client;

use App\Enums\ContactMessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactUsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'message_type' => ['required', Rule::enum(ContactMessageType::class)],
            'message' => ['required', 'string'],
        ];
    }
}
