<?php

namespace App\Http\Requests\Client;

use App\Enums\ContactMessageType;
use App\Rules\ValidDialCode;
use App\Rules\ValidMobilePhone;
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
            'country_code' => ['required', new ValidDialCode(), 'bail'],
            'phone' => ['required', 'string', 'max:50', new ValidMobilePhone($this->country_code)],
            'email' => ['required', 'email', 'max:255'],
            'message_type' => ['required', Rule::enum(ContactMessageType::class)],
            'message' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => ltrim($this->input('phone', ''), '0'),
        ]);
    }
}
