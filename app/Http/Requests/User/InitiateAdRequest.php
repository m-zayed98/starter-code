<?php

namespace App\Http\Requests\User;

use App\Enums\AdvertiserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class InitiateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $advertiserType = $this->input('advertiser_type');

        $requiresCommercialReg = in_array($advertiserType, [
            AdvertiserType::DEVELOPER->value,
            AdvertiserType::INVESTOR->value,
        ], true);

        return [
            // ── FAL license (saved to user profile) ───────────────────────
            'fal_license_number' => [
                'required',
                'digits_between:6,12',
            ],

            // ── NHC mobile (saved to user profile) ────────────────────────
            'nhc_mobile' => [
                'required',
                'digits:10',
                'regex:/^05/',
            ],

            // ── Advertiser type (saved to user profile) ───────────────────
            'advertiser_type' => [
                'required',
                new Enum(AdvertiserType::class),
            ],

            // ── Commercial registration (required for developer / investor) ─
            'commercial_registration_number' => [
                Rule::requiredIf($requiresCommercialReg),
                'nullable',
                'string',
                'max:255',
            ],

            // ── Commercial registration document ──────────────────────────
            'commercial_registration_file' => [
                Rule::requiredIf($requiresCommercialReg),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120', // 5 MB
            ],

            // ── Per-ad advertisement license number ───────────────────────
            'ad_license_number' => [
                'required',
                'digits_between:6,12',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fal_license_number.required'              => __('هذا الحقل إلزامي'),
            'fal_license_number.digits_between'        => __('رقم رخصة فال يجب أن يتكون من 6 إلى 12 رقماً'),
            'nhc_mobile.required'                      => __('هذا الحقل إلزامي'),
            'nhc_mobile.digits'                        => __('رقم الجوال يجب أن يتكون من 10 أرقام'),
            'nhc_mobile.regex'                         => __('رقم الجوال يجب أن يبدأ بـ 05'),
            'advertiser_type.required'                 => __('هذا الحقل إلزامي'),
            'advertiser_type.Illuminate\Validation\Rules\Enum' => __('نوع المعلن غير صحيح'),
            'commercial_registration_number.required'  => __('هذا الحقل إلزامي'),
            'commercial_registration_file.required'    => __('هذا الحقل إلزامي'),
            'commercial_registration_file.mimes'       => __('يجب أن يكون الملف بصيغة jpg أو png أو jpeg أو pdf'),
            'commercial_registration_file.max'         => __('الحد الأقصى لحجم الملف 5 MB'),
            'ad_license_number.required'               => __('هذا الحقل إلزامي'),
            'ad_license_number.digits_between'         => __('رقم رخصة الإعلان يجب أن يتكون من 6 إلى 12 رقماً'),
        ];
    }
}
