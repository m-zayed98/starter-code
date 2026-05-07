<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'rating'     => $this->rating,
            'feedback'   => $this->feedback,
            'user_name'  => $this->user?->name,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
