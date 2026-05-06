<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'blog_id'    => $this->blog_id,
            'user_id'    => $this->user_id,
            'user_name'  => $this->user?->name,
            'content'    => $this->content,
            'is_visible' => $this->is_visible,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
