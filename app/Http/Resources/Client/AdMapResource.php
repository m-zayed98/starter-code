<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Minimized resource for map pin usage.
 * Contains only the fields needed to render a map marker.
 */
class AdMapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $nhc = $this->nhc_data ?? [];

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'cover_image' => $this->getFirstMediaUrl('cover_image') ?: null,
            'price'       => $this->price,
            'latitude'    => $nhc['latitude']  ?? null,
            'longitude'   => $nhc['longitude'] ?? null,
        ];
    }
}
