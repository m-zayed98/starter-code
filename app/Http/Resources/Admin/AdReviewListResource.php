<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact resource for the admin ad reviews listing table.
 *
 * Columns per requirement:
 *  - user name
 *  - user phone
 *  - status (published | hidden)
 *  - ad name
 *  - rating (stars)
 *  - feedback text
 *  - created_at
 */
class AdReviewListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->is_visible ? 'published' : 'hidden';

        return [
            'id' => $this->id,
            'user_name' => $this->user?->name,
            'user_phone' => $this->user?->phone,
            'status' => $status,
            'status_label' => __($status),
            'ad_name' => $this->ad?->title,
            'rating' => $this->rating,
            'feedback' => $this->feedback,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
