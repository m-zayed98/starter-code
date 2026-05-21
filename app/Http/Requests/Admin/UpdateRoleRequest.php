<?php

namespace App\Http\Requests\Admin;

use App\Rules\UniqueTranslation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:20', new UniqueTranslation(table: 'roles', column: 'name', locale: 'en', ignoreId: $this->role)],
            'name.ar' => ['required', 'string', 'max:20', new UniqueTranslation(table: 'roles', column: 'name', locale: 'ar', ignoreId: $this->role)],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }
}
