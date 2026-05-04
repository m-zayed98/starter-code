<?php

namespace App\Models;

use App\Enums\AdPackageType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class AdPackage extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'type',
        'name',
        'ads_count',
        'duration_days',
        'price',
        'is_active',
        'start_date',
        'end_date',
        'max_subscribers',
    ];

    protected function casts(): array
    {
        return [
            'type'       => AdPackageType::class,
            'price'      => 'decimal:2',
            'is_active'  => 'boolean',
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)
            ->where('is_cancelled', false)
            ->where('expires_at', '>=', now()->toDateString());
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    /**
     * Order offer packages first, then by created_at DESC.
     */
    public function scopeOfferFirst(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(type, 'offer', 'normal')")
            ->orderBy('created_at', 'desc');
    }

    /**
     * Packages that are visible to users in the app.
     * Normal: is_active = true
     * Offer:  is_active = true AND end_date >= today AND active subscribers < max_subscribers
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->where('type', AdPackageType::NORMAL)
                    ->orWhere(function (Builder $offerQuery) {
                        $offerQuery->where('type', AdPackageType::OFFER)
                            ->where('end_date', '>=', now()->toDateString())
                            ->whereRaw(
                                '(max_subscribers IS NULL OR (
                                    SELECT COUNT(*) FROM subscriptions
                                    WHERE subscriptions.ad_package_id = ad_packages.id
                                    AND subscriptions.is_cancelled = 0
                                    AND subscriptions.expires_at >= ?
                                ) < max_subscribers)',
                                [now()->toDateString()]
                            );
                    });
            });
    }
}
