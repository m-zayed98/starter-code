<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact resource used in the admin ads listing table.
 *
 * Columns shown per requirement:
 *  - ad title
 *  - advertiser name (from nhc_data)
 *  - advertiser type (from user)
 *  - location: region + city + district (single column)
 *  - price
 *  - status
 */
class AdListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $nhc = $this->nhc_data ?? [];

        return [
            'id' => $this->id,
            'title' => $this->title,

            // Advertiser info
            'advertiser_name' => $nhc['advertiser_name'] ?? null,
            'advertiser_type' => $this->user?->advertiser_type?->value,
            'advertiser_type_label' => $this->user?->advertiser_type?->label(),

            // Location (region + city + district in one column)
            'location' => implode(' - ', array_filter([
                $nhc['region'] ?? null,
                $nhc['city'] ?? null,
                $nhc['district'] ?? null,
            ])),

            // Pricing
            'price' => $this->price,

            // Status
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
