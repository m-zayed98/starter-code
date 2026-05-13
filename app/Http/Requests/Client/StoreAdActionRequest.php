<?php

namespace App\Http\Requests\Client;

use App\Enums\AdActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAdActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', new Enum(AdActionType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('نوع الإجراء مطلوب.'),
            'type.Illuminate\Validation\Rules\Enum' => __('نوع الإجراء غير صالح. القيم المقبولة: call, whatsapp.'),
        ];
    }
}
