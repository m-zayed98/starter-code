<?php

namespace App\Models;

use App\Enums\AdPurpose;
use App\Enums\AdStatus;
use App\Enums\ApartmentCondition;
use App\Enums\FurnishingStatus;
use App\Enums\RentalPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ad extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        // ── Ownership ──────────────────────────────────────────────────────
        'user_id',
        'package_id',

        // ── FAL / NHC identifiers ──────────────────────────────────────────
        'fal_license_number',
        'ad_license_number',

        // ── NHC data (stored as JSON) ──────────────────────────────────────
        'nhc_data',

        // ── Ad meta ───────────────────────────────────────────────────────
        'status',
        'purpose',

        // ── Step 2: user-editable fields ──────────────────────────────────
        'title',
        'description',
        'apartment_condition',
        'deed_number',

        // ── Step 3: apartment details ─────────────────────────────────────
        'living_rooms_count',
        'bathrooms_count',
        'floor',
        'furnishing_status',

        // ── Pricing ───────────────────────────────────────────────────────
        'price',
        'rental_period',
    ];

    protected function casts(): array
    {
        return [
            'nhc_data'            => 'array',
            'status'              => AdStatus::class,
            'purpose'             => AdPurpose::class,
            'apartment_condition' => ApartmentCondition::class,
            'furnishing_status'   => FurnishingStatus::class,
            'rental_period'       => RentalPeriod::class,
            'price'               => 'decimal:2',
        ];
    }

    // ─── Media Collections ────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        // Single cover image for the ad listing
        $this->addMediaCollection('cover_image')
            ->singleFile();

        // Up to 10 apartment photos
        $this->addMediaCollection('apartment_images');

        // Optional video
        $this->addMediaCollection('apartment_video')
            ->singleFile();
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adPackage(): BelongsTo
    {
        return $this->belongsTo(AdPackage::class, 'package_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AdReview::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(AdReport::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(AdAction::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /**
     * Whether the ad has been fully published (all steps completed).
     */
    public function isPublished(): bool
    {
        return $this->status === AdStatus::PUBLISHED;
    }

    /**
     * Whether the ad is still in draft (step 1 done, step 2/3/4 pending).
     */
    public function isDraft(): bool
    {
        return $this->status === AdStatus::DRAFT;
    }

    // ─── Mutators / Computed Attributes ──────────────────────────────────

    /**
     * Whether the ad is currently published.
     * Reads from withCount('actions as views_count') if available,
     * otherwise falls back to the status enum.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === AdStatus::PUBLISHED;
    }

    /**
     * Average rating — prefers the eager-loaded aggregate (average_rating from withAvg)
     * or the withCount result; falls back to computing from the loaded reviews relation.
     * Never runs a query inside the resource.
     */
    public function getAverageRatingAttribute(): ?float
    {
        // Set by withAvg('reviews', 'rating') or manually assigned
        if (array_key_exists('average_rating', $this->attributes)) {
            $val = $this->attributes['average_rating'];
            return $val !== null ? round((float) $val, 1) : null;
        }

        // Fall back to loaded reviews relation (no extra query)
        if ($this->relationLoaded('reviews') && $this->reviews->isNotEmpty()) {
            return round($this->reviews->avg('rating'), 1);
        }

        return null;
    }

    /**
     * Reviews count — prefers the eager-loaded aggregate (reviews_count from withCount).
     * Falls back to the loaded reviews relation count.
     */
    public function getReviewsCountAttribute(): int
    {
        if (array_key_exists('reviews_count', $this->attributes)) {
            return (int) $this->attributes['reviews_count'];
        }

        if ($this->relationLoaded('reviews')) {
            return $this->reviews->count();
        }

        return 0;
    }

    /**
     * Views count — prefers the eager-loaded aggregate (views_count from withCount).
     */
    public function getViewsCountAttribute(): int
    {
        return (int) ($this->attributes['views_count'] ?? 0);
    }

    /**
     * Calls count — prefers the eager-loaded aggregate (calls_count from withCount).
     */
    public function getCallsCountAttribute(): int
    {
        return (int) ($this->attributes['calls_count'] ?? 0);
    }

    /**
     * WhatsApp count — prefers the eager-loaded aggregate (whatsapp_count from withCount).
     */
    public function getWhatsappCountAttribute(): int
    {
        return (int) ($this->attributes['whatsapp_count'] ?? 0);
    }
}
