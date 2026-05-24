<?php

namespace App\Http\Requests\User;

use App\Enums\AdPurpose;
use App\Enums\AdStatus;
use App\Enums\ApartmentCondition;
use App\Enums\FurnishingStatus;
use App\Enums\RentalPeriod;
use App\Repositories\Contracts\AdRepositoryContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateAdRequest extends FormRequest
{
    public function __construct(
        private readonly AdRepositoryContract $adRepository,
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Determine whether the ad being updated is still in draft status.
     */
    private function adIsDraft(): bool
    {
        $adId = (int) $this->route('ad');
        $ad = $this->adRepository->show($adId);

        return $ad !== null && $ad->status === AdStatus::DRAFT;
    }

    public function rules(): array
    {
        $purpose = $this->input('purpose');
        $isDraft = $this->adIsDraft();

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
                Rule::requiredIf($isDraft),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120', // 5 MB
            ],

            'apartment_images' => [
                Rule::requiredIf($isDraft),
                'nullable',
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
}
