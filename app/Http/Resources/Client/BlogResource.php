<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
            'id'              => $this->id,
            'name'            => $this->getTranslation('name', $locale),
            'description'     => $this->getTranslation('description', $locale),
            'content'         => $this->getTranslation('content', $locale),
            'main_image'      => $this->getFirstMediaUrl("main_image_{$locale}"),
            'meta_title'      => $this->getTranslation('meta_title', $locale),
            'meta_description' => $this->getTranslation('meta_description', $locale),
            'image_alt'       => $this->getTranslation('image_alt', $locale),
            'comments'        => $this->whenLoaded('comments', CommentResource::collection($this->comments->where('is_visible', true))),
            'created_at'      => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
