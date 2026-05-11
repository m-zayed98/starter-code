<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full detail resource for the admin ad detail view.
 */
class AdDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $nhc = $this->nhc_data ?? [];

        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),

            // ── Advertiser ────────────────────────────────────────────────
            'advertiser' => [
                'id' => $this->user?->id,
                'name' => $nhc['advertiser_name'] ?? $this->user?->name,
                'phone' => $nhc['phone_number'] ?? $this->user?->phone,
                'advertiser_type' => $this->user?->advertiser_type?->value,
                'advertiser_type_label' => $this->user?->advertiser_type?->label(),
            ],

            // ── Ad meta ───────────────────────────────────────────────────
            'purpose' => $this->purpose?->value,
            'purpose_label' => $this->purpose?->label(),
            'apartment_condition' => $this->apartment_condition?->value,
            'apartment_condition_label' => $this->apartment_condition?->label(),
            'title' => $this->title,
            'description' => $this->description,
            'fal_license_number' => $this->fal_license_number,
            'ad_license_number' => $this->ad_license_number,

            // ── Location ──────────────────────────────────────────────────
            'region' => $nhc['region'] ?? null,
            'city' => $nhc['city'] ?? null,
            'district' => $nhc['district'] ?? null,
            'latitude' => $nhc['latitude'] ?? null,
            'longitude' => $nhc['longitude'] ?? null,

            // ── Apartment specs ───────────────────────────────────────────
            'property_area' => $nhc['property_area'] ?? null,
            'number_of_rooms' => $nhc['number_of_rooms'] ?? null,
            'living_rooms_count' => $this->living_rooms_count,
            'bathrooms_count' => $this->bathrooms_count,
            'floor' => $this->floor,
            'property_face' => $nhc['property_face'] ?? null,
            'furnishing_status' => $this->furnishing_status?->value,
            'furnishing_status_label' => $this->furnishing_status?->label(),

            // ── Pricing ───────────────────────────────────────────────────
            'price' => $this->price,
            'rental_period' => $this->rental_period?->value,
            'rental_period_label' => $this->rental_period?->label(),

            // ── Media ─────────────────────────────────────────────────────
            'cover_image' => $this->getFirstMediaUrl('cover_image') ?: null,
            'apartment_images' => $this->getMedia('apartment_images')
                ->map(fn ($media) => $media->getFullUrl())
                ->values()
                ->toArray(),
            'apartment_video' => $this->getFirstMediaUrl('apartment_video') ?: null,

            // ── Reviews ───────────────────────────────────────────────────
            'reviews_count' => $this->reviews->count(),
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'feedback' => $review->feedback,
                    'user_name' => $review->user?->name,
                    'created_at' => $review->created_at?->format('Y-m-d H:i'),
                ]);
            }),

            // ── Reports count ─────────────────────────────────────────────
            'reports_count' => $this->reports_count ?? 0,

            // ── Timestamps ────────────────────────────────────────────────
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
