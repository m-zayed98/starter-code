<?php

namespace App\Http\Requests\User;

use App\Enums\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ProcessTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Only terminal statuses are accepted from the gateway callback.
            // pending is excluded because a transaction can never be reset to pending.
            'status'    => ['required', new Enum(TransactionStatus::class), 'not_in:' . TransactionStatus::PENDING->value],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
