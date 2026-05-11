<?php

namespace App\Repositories\Contracts;

use App\Models\AdReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    /**
     * Return a paginated list of all reviews for the admin panel.
     * Applies AdminAdReviewFilter, eager-loads user and ad.
     */
    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator;
}
