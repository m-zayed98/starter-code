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
                'regex:/^\d{6,12}$/',
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
            // Must be purely numeric, between 6 and 12 digits (no decimals, no signs).
            'ad_license_number' => [
                'required',
                'regex:/^\d{6,12}$/',
            ],
        ];
    }
}
