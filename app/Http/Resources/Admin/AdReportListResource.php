<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact resource for the admin ad reports listing table.
 *
 * Columns per requirement:
 *  - user name
 *  - user phone
 *  - ad name
 *  - reason
 *  - status (replied | pending)
 */
class AdReportListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user?->name,
            'user_phone' => $this->user?->phone,
            'ad_name' => $this->ad?->title,
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => __($this->status),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
