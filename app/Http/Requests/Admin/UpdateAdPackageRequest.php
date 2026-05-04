<?php

namespace App\Http\Requests\Admin;

use App\Enums\AdPackageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'            => ['required', Rule::enum(AdPackageType::class)],
            'name'            => ['required', 'array'],
            'name.en'         => ['required', 'string', 'between:5,50'],
            'name.ar'         => ['required', 'string', 'between:5,50'],
            'ads_count'       => ['required', 'integer', 'gt:0'],
            'duration_days'   => ['required', 'integer', 'min:1', 'max:1000'],
            'price'           => ['required', 'numeric', 'gt:0'],
            'start_date'      => ['required_if:type,offer', 'nullable', 'date', 'after_or_equal:today'],
            'end_date'        => ['required_if:type,offer', 'nullable', 'date', 'after:start_date'],
            'max_subscribers' => ['required_if:type,offer', 'nullable', 'integer', 'min:1'],
        ];
    }
}
