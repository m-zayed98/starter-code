<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        [$resource, $action] = explode(config('permission.separator'), $this->name);
        return [
            'id'            => $this->id,
            'resource'      => $resource,
            'action'        => $action,
            'created_at'    => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
