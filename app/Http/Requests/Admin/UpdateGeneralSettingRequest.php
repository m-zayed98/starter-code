<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_free_period_enabled' => ['required', 'boolean'],
            'free_period_start_date' => ['required_if:is_free_period_enabled,true', 'nullable', 'date', 'after_or_equal:now'],
            'free_period_end_date' => ['required_if:is_free_period_enabled,true', 'nullable', 'date', 'after:free_period_start_date'],
            'free_period_reason_ar' => ['required_if:is_free_period_enabled,true', 'nullable', 'string', 'min:10', 'max:500'],
            'free_period_reason_en' => ['required_if:is_free_period_enabled,true', 'nullable', 'string', 'min:10', 'max:500'],
        ];
    }
}
