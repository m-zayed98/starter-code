<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationGroupDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_merge(
            NotificationGroupResource::make($this)->resolve($request),
            [
                'recipients' => $this->whenLoaded(
                    'recipients',
                    fn () => UserResource::collection($this->recipients)
                ),
            ]
        );
    }
}

