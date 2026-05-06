<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'min:1', 'max:255'],
            'title.ar' => ['required', 'string', 'min:1', 'max:255'],

            'body' => ['required', 'array'],
            'body.en' => ['required', 'string', 'min:1'],
            'body.ar' => ['required', 'string', 'min:1'],

            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ];
    }
}

