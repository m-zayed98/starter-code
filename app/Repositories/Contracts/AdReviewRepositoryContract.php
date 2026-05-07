<?php

namespace App\Repositories\Contracts;

use App\Models\AdReview;

interface AdReviewRepositoryContract extends RepositoryContract
{
    /**
     * Check whether a user has already reviewed a specific ad.
     */
    public function hasUserReviewedAd(int $adId, int $userId): bool;

    /**
     * Find a user's review for a specific ad.
     */
    public function findByAdAndUser(int $adId, int $userId): ?AdReview;

    /**
     * Get the average rating for an ad.
     */
    public function averageRatingForAd(int $adId): float;

    /**
     * Get the total review count for an ad.
     */
    public function countForAd(int $adId): int;
}
