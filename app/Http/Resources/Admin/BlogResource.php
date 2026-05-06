<?php

namespace App\Http\Resources\Admin;

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
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'name_ar'            => $this->getTranslation('name', 'ar'),
            'name_en'            => $this->getTranslation('name', 'en'),
            'description'        => $this->description,
            'description_ar'     => $this->getTranslation('description', 'ar'),
            'description_en'     => $this->getTranslation('description', 'en'),
            'content'            => $this->content,
            'content_ar'         => $this->getTranslation('content', 'ar'),
            'content_en'         => $this->getTranslation('content', 'en'),
            'main_image_ar'      => $this->getFirstMediaUrl('main_image_ar'),
            'main_image_en'      => $this->getFirstMediaUrl('main_image_en'),
            'meta_title'         => $this->meta_title,
            'meta_title_ar'      => $this->getTranslation('meta_title', 'ar'),
            'meta_title_en'      => $this->getTranslation('meta_title', 'en'),
            'meta_description'   => $this->meta_description,
            'meta_description_ar' => $this->getTranslation('meta_description', 'ar'),
            'meta_description_en' => $this->getTranslation('meta_description', 'en'),
            'image_alt'          => $this->image_alt,
            'image_alt_ar'       => $this->getTranslation('image_alt', 'ar'),
            'image_alt_en'       => $this->getTranslation('image_alt', 'en'),
            'is_active'          => $this->is_active,
            'comments'           => $this->whenLoaded('comments' , CommentResource::collection($this->comments)),
            'created_at'         => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'         => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
