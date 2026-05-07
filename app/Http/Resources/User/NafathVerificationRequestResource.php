<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NafathVerificationRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'trans_id'    => $this->trans_id,
            'random_code' => $this->random_code,
            'status'      => $this->status->value,
            'expires_at'  => $this->expires_at?->toIso8601String(),
            'created_at'  => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
