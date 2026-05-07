<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'              => $this->id,
            'status'          => $this->status->value,
            'status_label'    => $this->status->label(),
            'ad_count'        => $this->ad_count,
            'user_ads_count'  => $this->user_ads_count,
            'remaining_quota' => $this->remainingQuota(),
            'package_price'   => $this->package_price,
            'starts_at'       => $this->starts_at?->format('Y-m-d'),
            'expires_at'      => $this->expires_at?->format('Y-m-d'),
            'is_active'       => $this->isActive(),
            'is_expired'      => $this->isExpired(),
            'can_be_used'     => $this->canBeUsed(),
            'package'         => $this->whenLoaded('adPackage', function () use ($locale) {
                return [
                    'id'           => $this->adPackage->id,
                    'name'         => $this->adPackage->getTranslation('name', $locale),
                    'type'         => $this->adPackage->type->value,
                    'duration_days'=> $this->adPackage->duration_days,
                ];
            }),
            'created_at'      => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
