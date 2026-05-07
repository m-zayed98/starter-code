<?php

namespace App\Http\Resources\Client;

use App\Enums\AdPackageType;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Client-facing Ad Package resource.
 *
 * Dynamic properties stamped by AdPackageController before transformation:
 *   - is_subscribed      (bool)              – whether the auth user is subscribed
 *   - active_subscription (Subscription|null) – the user's active subscription model
 *
 * All calculations (usage_percent, remaining_quota, etc.) are delegated to
 * model helper methods — no logic lives here.
 */
class AdPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        $data = [
            'id'            => $this->id,
            'name'          => $this->getTranslation('name', $locale),
            'type'          => $this->type->value,
            'type_label'    => $this->type->label(),
            'price'         => $this->price,
            'ads_count'     => $this->ads_count,
            'duration_days' => $this->duration_days,
            'image'         => $this->getFirstMediaUrl('image') ?: null,
            'is_subscribed' => (bool) ($this->is_subscribed ?? false),
        ];


        if ($this->type === AdPackageType::OFFER) {
            $activeCount = $this->activeSubscriptions->count();

            $data['start_date']            = $this->start_date?->format('Y-m-d');
            $data['end_date']              = $this->end_date?->format('Y-m-d');
            $data['max_subscribers']       = $this->max_subscribers;
            $data['active_subscribers']    = $activeCount;
            $data['remaining_subscribers'] = $this->max_subscribers !== null
                ? max(0, $this->max_subscribers - $activeCount)
                : null;
        }

        return $data;
    }
}
