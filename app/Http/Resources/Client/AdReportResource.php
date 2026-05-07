<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'ad_id'      => $this->ad_id,
            'reason'     => $this->reason,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
