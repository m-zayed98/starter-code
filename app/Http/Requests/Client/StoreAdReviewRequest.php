<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => __('هذا الحقل إلزامي'),
            'rating.min'      => __('التقييم يجب أن يكون بين 1 و 5'),
            'rating.max'      => __('التقييم يجب أن يكون بين 1 و 5'),
            'feedback.max'    => __('الحد الأقصى للتعليق 500 حرف'),
        ];
    }
}
