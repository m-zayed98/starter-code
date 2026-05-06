<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->getTranslation('title', 'ar'),
            'title_en' => $this->getTranslation('title', 'en'),
            'body' => $this->body,
            'body_ar' => $this->getTranslation('body', 'ar'),
            'body_en' => $this->getTranslation('body', 'en'),
            'status' => $this->status,
            'sent_at' => $this->sent_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ]),
            'recipients_count' => $this->whenCounted('recipients'),
        ];
    }
}

