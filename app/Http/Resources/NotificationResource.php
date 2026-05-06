<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        // Get localized title and body if they exist
        $title = $this->data['title'] ?? null;
        $body = $this->data['body'] ?? null;

        // If title/body are arrays with translations, get the localized version
        if (is_array($title)) {
            $title = $title[$locale] ?? $title['en'] ?? $title['ar'] ?? null;
        }

        if (is_array($body)) {
            $body = $body[$locale] ?? $body['en'] ?? $body['ar'] ?? null;
        }

        return [
            'id' => $this->id,
            'type' => $this->data['type'] ?? $this->type,
            'title' => $title,
            'body' => $body,
            'data' => $this->data,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'is_read' => !is_null($this->read_at),
        ];
    }
}
