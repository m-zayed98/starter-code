<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => __('هذا الحقل إلزامي'),
            'reason.min'      => __('سبب الإبلاغ يجب أن يكون 10 أحرف على الأقل'),
            'reason.max'      => __('الحد الأقصى لسبب الإبلاغ 500 حرف'),
        ];
    }
}
