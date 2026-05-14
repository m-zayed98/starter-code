<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// AdReviewResource is in the same namespace — referenced directly below

/**
 * Full detail view — used in the public show endpoint.
 * Renders all fields per the US detail page spec.
 */
class AdDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $nhc = $this->nhc_data ?? [];

        return [
            'id'                  => $this->id,

            // ── Advertiser ────────────────────────────────────────────────
            'advertiser'          => [
                'id'       => $this->user?->id,
                'name'     => $this->user?->name,
                'phone'    => $this->user?->full_phone,
                'whatsapp' => $this->user?->full_phone,
                'image'    => $this->user?->avatar,
            ],

            // ── Deprecated flat fields (kept for backwards compatibility) ─
            'advertiser_name'     => $nhc['advertiser_name'] ?? null,
            'advertiser_phone'    => $nhc['phone_number'] ?? null,

            // ── Ad meta ───────────────────────────────────────────────────
            'purpose'             => $this->purpose?->value,
            'purpose_label'       => $this->purpose?->label(),
            'title'               => $this->title,
            'description'         => $this->description,
            'apartment_condition' => $this->apartment_condition?->value,
            'apartment_condition_label' => $this->apartment_condition?->label(),

            // ── Location ──────────────────────────────────────────────────
            'region'              => $nhc['region']   ?? null,
            'city'                => $nhc['city']     ?? null,
            'district'            => $nhc['district'] ?? null,
            'latitude'            => $nhc['latitude']  ?? null,
            'longitude'           => $nhc['longitude'] ?? null,
            'location_description_on_moj_deed' => $nhc['location_description_on_moj_deed'] ?? null,

            // ── Physical attributes ───────────────────────────────────────
            'property_area'       => $nhc['property_area']   ?? null,
            'number_of_rooms'     => $nhc['number_of_rooms']  ?? null,
            'living_rooms_count'  => $this->living_rooms_count,
            'bathrooms_count'     => $this->bathrooms_count,
            'floor'               => $this->floor,
            'property_face'       => $nhc['property_face']   ?? null,
            'street_width'        => $nhc['street_width']    ?? null,
            'furnishing_status'   => $this->furnishing_status?->value,
            'furnishing_label'    => $this->furnishing_status?->label(),

            // ── Property classification ───────────────────────────────────
            'property_type'       => $nhc['property_type']   ?? null,
            'property_age'        => $nhc['property_age']    ?? null,
            'property_usages'     => $nhc['property_usages'] ?? [],
            'property_utilities'  => $nhc['property_utilities'] ?? [],

            // ── Pricing ───────────────────────────────────────────────────
            'price'               => $this->price,
            'rental_period'       => $this->rental_period?->value,
            'rental_period_label' => $this->rental_period?->label(),
            'property_price'      => $nhc['property_price'] ?? null,

            // ── Legal / obligations ───────────────────────────────────────
            'deed_number'         => $this->when(
                $this->purpose?->value === 'sale',
                $this->deed_number,
            ),
            'plan_number'         => $nhc['plan_number']  ?? null,
            'land_number'         => $nhc['land_number']  ?? null,
            'guarantees_and_their_duration'   => $nhc['guarantees_and_their_duration']  ?? null,
            'obligations_on_the_property'     => $nhc['obligations_on_the_property']    ?? null,
            'ownership_transfer_fee_type'     => $nhc['ownership_transfer_fee_type']    ?? null,

            // ── Responsible employee ──────────────────────────────────────
            'responsible_employee_name'         => $nhc['responsible_employee_name']         ?? null,
            'responsible_employee_phone_number' => $nhc['responsible_employee_phone_number'] ?? null,

            // ── License info ──────────────────────────────────────────────
            'fal_license_number'  => $this->fal_license_number,
            'ad_license_number'   => $this->ad_license_number,
            'ad_license_url'      => $nhc['ad_license_url']  ?? null,
            'is_nhc_verified'     => (bool) ($nhc['is_valid'] ?? false),
            'nhc_creation_date'   => $nhc['creation_date'] ?? null,
            'nhc_end_date'        => $nhc['end_date']       ?? null,
            'nhc_mobile'          => $this->user?->nhc_mobile,

            // ── Media ─────────────────────────────────────────────────────
            'cover_image'         => $this->getFirstMediaUrl('cover_image') ?: null,
            'apartment_images'    => $this->getMedia('apartment_images')
                ->map(fn($m) => $m->getFullUrl())
                ->values()
                ->toArray(),
            'apartment_video'     => $this->getFirstMediaUrl('apartment_video') ?: null,

            // ── Reviews summary ───────────────────────────────────────────
            'reviews_count'       => $this->reviews_count ?? $this->reviews->count(),
            'average_rating'      => $this->average_rating
                ?? ($this->reviews->count()
                    ? round($this->reviews->avg('rating'), 1)
                    : null),
            'reviews'             => $this->whenLoaded(
                'reviews',
                AdReviewResource::collection($this->reviews),
            ),

            // ── Action counts ─────────────────────────────────────────────
            'views_count'         => $this->views_count    ?? 0,
            'calls_count'         => $this->calls_count    ?? 0,
            'whatsapp_count'      => $this->whatsapp_count ?? 0,

            // ── Auth user interaction flags ───────────────────────────────
            'has_review'          => (bool) ($this->has_review ?? 0),
            'has_report'          => (bool) ($this->has_report ?? 0),

            'created_at'          => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
