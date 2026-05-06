<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'array'],
            'name.ar'             => ['required', 'string', 'min:1'],
            'name.en'             => ['required', 'string', 'min:1'],
            'description'         => ['required', 'array'],
            'description.ar'      => ['required', 'string', 'max:200'],
            'description.en'      => ['required', 'string', 'max:200'],
            'content'             => ['required', 'array'],
            'content.ar'          => ['required', 'string'],
            'content.en'          => ['required', 'string'],
            'main_image_ar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'main_image_en'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'meta_title'          => ['nullable', 'array'],
            'meta_title.ar'       => ['nullable', 'string'],
            'meta_title.en'       => ['nullable', 'string'],
            'meta_description'    => ['nullable', 'array'],
            'meta_description.ar' => ['nullable', 'string'],
            'meta_description.en' => ['nullable', 'string'],
            'image_alt'           => ['nullable', 'array'],
            'image_alt.ar'        => ['nullable', 'string'],
            'image_alt.en'        => ['nullable', 'string'],
        ];
    }
}
