<?php

namespace App\Http\Requests\User;

use App\Enums\NafathVerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NafathCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trans_id' => ['required', 'string'],
            'status'   => [
                'required',
                'string',
                Rule::in([
                    NafathVerificationStatus::APPROVED->value,
                    NafathVerificationStatus::REJECTED->value,
                    NafathVerificationStatus::EXPIRED->value,
                ]),
            ],
        ];
    }
}
