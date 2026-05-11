<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full detail resource for the admin ad report detail view.
 *
 * Includes:
 *  - user name, phone
 *  - ad name, ad owner name
 *  - reason
 *  - reply (if replied)
 *  - status
 */
class AdReportDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user?->name,
            'user_phone' => $this->user?->phone,
            'user_email' => $this->user?->email,
            'ad_name' => $this->ad?->title,
            'ad_owner_name' => $this->ad?->user?->name,
            'reason' => $this->reason,
            'reply' => $this->reply,
            'status' => $this->status,
            'status_label' => __($this->status),
            'replied_at' => $this->replied_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
