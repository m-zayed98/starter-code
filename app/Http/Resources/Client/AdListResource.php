<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Card view — used in the public listing (paginated, 10 per page).
 * Contains only the fields needed to render a list card per the US.
 */
class AdListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $nhc = $this->nhc_data ?? [];

        return [
            'id'                => $this->id,

            // ── Card header ───────────────────────────────────────────────
            'title'             => $this->title,
            'advertiser_name'   => $nhc['advertiser_name'] ?? null,   // اسم المعلن

            // ── Ad type & condition ───────────────────────────────────────
            'purpose'           => $this->purpose?->value,            // sale | rent
            'purpose_label'     => $this->purpose?->label(),
            'apartment_condition' => $this->apartment_condition?->value,
            'apartment_condition_label' => $this->apartment_condition?->label(),

            // ── Pricing ───────────────────────────────────────────────────
            'price'             => $this->price,
            'rental_period'     => $this->rental_period?->value,
            'rental_period_label' => $this->rental_period?->label(),

            // ── Location (from nhc_data) ──────────────────────────────────
            'region'            => $nhc['region']   ?? null,          // المنطقة
            'city'              => $nhc['city']     ?? null,          // المدينة
            'district'          => $nhc['district'] ?? null,          // الحي

            // ── Physical ─────────────────────────────────────────────────
            'property_area'     => $nhc['property_area'] ?? null,     // المساحة (م²)
            'floor'             => $this->floor,                      // الدور
            'furnishing_status' => $this->furnishing_status?->value,  // مفروشة؟
            'furnishing_label'  => $this->furnishing_status?->label(),

            // ── Cover image ───────────────────────────────────────────────
            'cover_image'       => $this->getFirstMediaUrl('cover_image') ?: null,

            // ── NHC verification badge ────────────────────────────────────
            'is_nhc_verified'   => (bool) ($nhc['is_valid'] ?? false),

            // ── Rating summary ────────────────────────────────────────────
            'reviews_count'     => $this->reviews_count ?? 0,
            'average_rating'    => $this->average_rating ?? null,

            // ── Action counts ─────────────────────────────────────────────
            'views_count'       => $this->views_count    ?? 0,
            'calls_count'       => $this->calls_count    ?? 0,
            'whatsapp_count'    => $this->whatsapp_count ?? 0,

            'created_at'        => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
