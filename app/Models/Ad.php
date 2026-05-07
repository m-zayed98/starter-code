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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ad extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        // ── Ownership ──────────────────────────────────────────────────────
        'user_id',

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
}
