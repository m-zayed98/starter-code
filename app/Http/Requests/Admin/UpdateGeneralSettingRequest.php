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

    public function messages(): array
    {
        return [
            'free_period_start_date.required_if' => __('Start date is required when free period is enabled'),
            'free_period_start_date.after_or_equal' => __('Start date must be current time or later'),
            'free_period_end_date.required_if' => __('End date is required when free period is enabled'),
            'free_period_end_date.after' => __('End date must be after start date'),
            'free_period_reason_ar.required_if' => __('Arabic reason is required when free period is enabled'),
            'free_period_reason_ar.min' => __('Arabic reason must be at least 10 characters'),
            'free_period_reason_ar.max' => __('Arabic reason must not exceed 500 characters'),
            'free_period_reason_en.required_if' => __('English reason is required when free period is enabled'),
            'free_period_reason_en.min' => __('English reason must be at least 10 characters'),
            'free_period_reason_en.max' => __('English reason must not exceed 500 characters'),
        ];
    }
}
