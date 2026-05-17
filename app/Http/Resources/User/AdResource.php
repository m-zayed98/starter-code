<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,

            // ── Status & purpose ──────────────────────────────────────────
            'status'              => $this->status?->value,
            'status_label'        => $this->status?->label(),
            'is_published'        => $this->is_published,
            'purpose'             => $this->purpose?->value,
            'purpose_label'       => $this->purpose?->label(),

            // ── FAL / NHC identifiers ─────────────────────────────────────
            'fal_license_number'  => $this->fal_license_number,
            'ad_license_number'   => $this->ad_license_number,

            // ── NHC data (all fields returned from NHC) ───────────────────
            'nhc_data'            => $this->nhc_data,

            // ── Step 2: user-editable fields ──────────────────────────────
            'title'               => $this->title,
            'description'         => $this->description,
            'apartment_condition' => $this->apartment_condition?->value,
            'deed_number'         => $this->when(
                $this->purpose?->value === 'sale',
                $this->deed_number,
            ),

            // ── Step 3: apartment details ─────────────────────────────────
            'living_rooms_count'  => $this->living_rooms_count,
            'bathrooms_count'     => $this->bathrooms_count,
            'floor'               => $this->floor,
            'furnishing_status'   => $this->furnishing_status?->value,

            // ── Pricing ───────────────────────────────────────────────────
            'price'               => $this->price,
            'rental_period'       => $this->rental_period?->value,
            'rental_period_label' => $this->rental_period?->label(),

            // ── Media ─────────────────────────────────────────────────────
            'cover_image'         => $this->getFirstMediaUrl('cover_image') ?: null,
            'apartment_images'    => $this->getMedia('apartment_images')
                ->map(fn($media) => $media->getFullUrl())
                ->values()
                ->toArray(),
            'apartment_video'     => $this->getFirstMediaUrl('apartment_video') ?: null,

            // ── Timestamps ────────────────────────────────────────────────
            'created_at'          => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'          => $this->updated_at?->format('Y-m-d H:i'),

            // ── Action counts ─────────────────────────────────────────────
            'views_count'         => $this->views_count,
            'calls_count'         => $this->calls_count,
            'whatsapp_count'      => $this->whatsapp_count,

            // ── Rating summary ────────────────────────────────────────────
            'reviews_count'       => $this->reviews_count,
            'average_rating'      => $this->average_rating,
        ];
    }
}
