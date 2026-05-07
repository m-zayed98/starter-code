<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'        => $this->id,
            'amount'    => $this->amount,
            'status'    => $this->status->value,
            'status_label' => $this->status->label(),
            'reference' => $this->reference,
            'package'   => $this->whenLoaded('transactionable', function () use ($locale) {
                return [
                    'id'   => $this->transactionable->id,
                    'name' => $this->transactionable->getTranslation('name', $locale),
                    'type' => $this->transactionable->type->value,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
