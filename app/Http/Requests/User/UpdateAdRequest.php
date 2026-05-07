<?php

namespace App\Http\Requests\User;

use App\Enums\AdPurpose;
use App\Enums\ApartmentCondition;
use App\Enums\FurnishingStatus;
use App\Enums\RentalPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $purpose = $this->input('purpose');

        return [
            // ── Step 2: Ad meta ───────────────────────────────────────────
            'purpose' => [
                'required',
                new Enum(AdPurpose::class),
            ],

            'title' => [
                'required',
                'string',
                'min:5',
                'max:50',
            ],

            'description' => [
                'required',
                'string',
                'max:500',
            ],

            'apartment_condition' => [
                'required',
                new Enum(ApartmentCondition::class),
            ],

            // Required only when purpose = sale
            'deed_number' => [
                Rule::requiredIf($purpose === AdPurpose::SALE->value),
                'nullable',
                'string',
                'max:255',
            ],

            // ── Step 3: Apartment details ─────────────────────────────────
            'living_rooms_count' => [
                'required',
                'integer',
                'min:1',
            ],

            'bathrooms_count' => [
                'required',
                'integer',
                'min:1',
            ],

            'floor' => [
                'required',
                'integer',
                'min:1',
            ],

            'furnishing_status' => [
                'required',
                new Enum(FurnishingStatus::class),
            ],

            // ── Pricing ───────────────────────────────────────────────────
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            // Required only when purpose = rent
            'rental_period' => [
                Rule::requiredIf($purpose === AdPurpose::RENT->value),
                'nullable',
                new Enum(RentalPeriod::class),
            ],

            // ── Step 4: Media ─────────────────────────────────────────────
            'cover_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120', // 5 MB
            ],

            'apartment_images' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'apartment_images.*' => [
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120', // 5 MB
            ],

            'apartment_video' => [
                'nullable',
                'file',
                'mimes:mp4,mov',
                'max:51200', // 50 MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'purpose.required'                  => __('هذا الحقل إلزامي'),
            'title.required'                    => __('هذا الحقل إلزامي'),
            'title.min'                         => __('عنوان الإعلان يجب أن يكون 5 أحرف على الأقل'),
            'title.max'                         => __('عنوان الإعلان يجب ألا يتجاوز 50 حرفاً'),
            'description.required'              => __('هذا الحقل إلزامي'),
            'description.max'                   => __('الوصف يجب ألا يتجاوز 500 حرف'),
            'apartment_condition.required'      => __('هذا الحقل إلزامي'),
            'deed_number.required'              => __('هذا الحقل إلزامي'),
            'living_rooms_count.required'       => __('هذا الحقل إلزامي'),
            'living_rooms_count.min'            => __('عدد الصالات يجب أن يكون 1 على الأقل'),
            'bathrooms_count.required'          => __('هذا الحقل إلزامي'),
            'bathrooms_count.min'               => __('عدد الحمامات يجب أن يكون 1 على الأقل'),
            'floor.required'                    => __('هذا الحقل إلزامي'),
            'floor.min'                         => __('الطابق يجب أن يكون 1 على الأقل'),
            'furnishing_status.required'        => __('هذا الحقل إلزامي'),
            'price.required'                    => __('هذا الحقل إلزامي'),
            'rental_period.required'            => __('هذا الحقل إلزامي'),
            'cover_image.required'              => __('هذا الحقل إلزامي'),
            'cover_image.max'                   => __('الحد الأقصى لحجم الملف 5 MB'),
            'cover_image.mimes'                 => __('يجب أن تكون الصورة بصيغة jpg أو jpeg أو png'),
            'apartment_images.required'         => __('هذا الحقل إلزامي'),
            'apartment_images.max'              => __('يمكن رفع 10 صور كحد أقصى'),
            'apartment_images.*.max'            => __('الحد الأقصى لحجم الملف 5 MB'),
            'apartment_images.*.mimes'          => __('يجب أن تكون الصورة بصيغة jpg أو jpeg أو png'),
            'apartment_video.max'               => __('الحد الأقصى لحجم الملف 50 MB'),
            'apartment_video.mimes'             => __('يجب أن يكون الفيديو بصيغة mp4 أو mov'),
        ];
    }
}
