<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ad_package_id',
        'ad_count',
        'user_ads_count',
        'package_price',
        'status',
        'starts_at',
        'expires_at',
        'is_cancelled',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'      => 'date',
            'expires_at'     => 'date',
            'is_cancelled'   => 'boolean',
            'status'         => SubscriptionStatus::class,
            'package_price'  => 'decimal:2',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adPackage(): BelongsTo
    {
        return $this->belongsTo(AdPackage::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transactionable_id')
            ->where('transactionable_type', self::class);
    }

    // ─── Helper Methods ───────────────────────────────────────────────────

    /**
     * Whether the subscription's end date has passed.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Whether the subscription is currently active (not cancelled, not expired).
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE
            && ! $this->is_cancelled
            && ! $this->isExpired();
    }

    /**
     * Whether the subscription can be used (active + has remaining quota).
     */
    public function canBeUsed(): bool
    {
        return $this->isActive() && $this->hasQuota();
    }

    /**
     * Whether the user still has ad quota remaining.
     */
    public function hasQuota(): bool
    {
        return $this->user_ads_count < $this->ad_count;
    }

    /**
     * Remaining ad slots.
     */
    public function remainingQuota(): int
    {
        return max(0, $this->ad_count - $this->user_ads_count);
    }

    /**
     * Usage percentage (0–100) for progress bar rendering.
     */
    public function usagePercent(): float
    {
        if ($this->ad_count <= 0) {
            return 0.0;
        }

        return round(($this->user_ads_count / $this->ad_count) * 100, 2);
    }
}
