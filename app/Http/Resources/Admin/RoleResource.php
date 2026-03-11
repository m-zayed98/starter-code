<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'name'        => $this->name,
            'name_ar'     => $this->getTranslation('name', 'ar'),
            'name_en'     => $this->getTranslation('name', 'en'),
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at->format('Y-m-d H:i'),
            'updated_at'  => $this->updated_at->format('Y-m-d H:i'),
            'permissions' => PermissionResource::collection($this->permissions),
        ];
    }
}
