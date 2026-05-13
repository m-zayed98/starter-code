<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'country_code'        => $this->country_code,
            'full_phone'          => $this->full_phone,
            'avatar'              => $this->avatar,
            'birth_date'          => $this->birth_date?->format('Y-m-d'),
            'identity_number'     => $this->identity_number,
            'verified_by_nafath'  => (bool) $this->verified_by_nafath,
            'fal_license_number'  => $this->fal_license_number,
            'nhc_mobile'           => $this->nhc_mobile,
            'advertiser_type'      => $this->advertiser_type,
            'commercial_registration_number' => $this->commercial_registration_number,
            'created_at'          => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'          => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
