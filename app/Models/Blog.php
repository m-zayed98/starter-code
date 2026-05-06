<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Blog extends Model implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;

    public array $translatable = [
        'name',
        'description',
        'content',
        'meta_title',
        'meta_description',
        'image_alt',
    ];

    protected $fillable = [
        'name',
        'description',
        'content',
        'meta_title',
        'meta_description',
        'image_alt',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    /**
     * Filter only active blogs.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Order blogs by created_at DESC (newest first).
     */
    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ─── Media Collections ────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image_ar')
            ->singleFile();

        $this->addMediaCollection('main_image_en')
            ->singleFile();
    }
}
