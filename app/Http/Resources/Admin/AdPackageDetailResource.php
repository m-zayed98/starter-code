<?php

namespace App\Http\Resources\Admin;

use App\Enums\AdPackageType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdPackageDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'                => $this->id,
            'name'              => $this->name,
            'name_ar'           => $this->getTranslation('name', 'ar'),
            'name_en'           => $this->getTranslation('name', 'en'),
            'type'              => $this->type->value,
            'type_label'        => $this->type->label(),
            'price'             => $this->price,
            'ads_count'         => $this->ads_count,
            'duration_days'     => $this->duration_days,
            'is_active'         => $this->is_active,
            'image'             => $this->getFirstMediaUrl('image') ?: null,
            'subscribers_count' => $this->activeSubscriptions->count(),
            'created_at'        => $this->created_at?->format('Y-m-d H:i'),
        ];

        // Offer-only fields
        if ($this->type === AdPackageType::OFFER) {
            $data['start_date']      = $this->start_date?->format('Y-m-d');
            $data['end_date']        = $this->end_date?->format('Y-m-d');
            $data['max_subscribers'] = $this->max_subscribers;
        }

        return $data;
    }
}
