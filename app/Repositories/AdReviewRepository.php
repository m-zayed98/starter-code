<?php

namespace App\Repositories;

use App\Models\AdReview;
use App\Repositories\Contracts\AdReviewRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class AdReviewRepository extends BaseRepository implements AdReviewRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new AdReview();
    }

    /**
     * Check whether a user has already reviewed a specific ad.
     */
    public function hasUserReviewedAd(int $adId, int $userId): bool
    {
        return $this->newQuery()
            ->where('ad_id', $adId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Find a user's review for a specific ad.
     */
    public function findByAdAndUser(int $adId, int $userId): ?AdReview
    {
        /** @var AdReview|null */
        return $this->newQuery()
            ->where('ad_id', $adId)
            ->where('user_id', $userId)
            ->with('user')
            ->first();
    }

    /**
     * Get the average rating for an ad.
     */
    public function averageRatingForAd(int $adId): float
    {
        return (float) $this->newQuery()
            ->where('ad_id', $adId)
            ->avg('rating');
    }

    /**
     * Get the total review count for an ad.
     */
    public function countForAd(int $adId): int
    {
        return $this->newQuery()
            ->where('ad_id', $adId)
            ->count();
    }
}
