<?php

namespace App\Http\Requests\Admin;

use App\Rules\UniqueTranslation;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => [
                'array',
                'required',
            ],
            'name.en'     => [
                'required',
                'string',
                'max:20',
                new UniqueTranslation(
                    table: 'roles',
                    column: 'name',
                    locale: 'en'
                ),
            ],
            'name.ar'     => [
                'required',
                'string',
                'max:20',
                new UniqueTranslation(
                    table: 'roles',
                    column: 'name',
                    locale: 'ar'
                )
            ],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Add custom messages here
        ];
    }
}
