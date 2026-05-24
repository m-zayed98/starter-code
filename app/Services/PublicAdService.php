<?php

namespace App\Services;

use App\Enums\AdActionType;
use App\Models\Ad;
use App\Models\AdReport;
use App\Models\AdReview;
use App\Repositories\Contracts\AdActionRepositoryContract;
use App\Repositories\Contracts\AdReportRepositoryContract;
use App\Repositories\Contracts\AdRepositoryContract;
use App\Repositories\Contracts\AdReviewRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Handles all public-facing ad operations:
 *  - Listing published ads (with filters)
 *  - Showing ad detail (records a view for authenticated users)
 *  - Submitting a review (once per user per ad)
 *  - Submitting a report (once per user per ad)
 */
class PublicAdService
{
    public function __construct(
        private readonly AdRepositoryContract $adRepository,
        private readonly AdReviewRepositoryContract $reviewRepository,
        private readonly AdReportRepositoryContract $reportRepository,
        private readonly AdActionRepositoryContract $adActionRepository,
    ) {}

    /**
     * Return a paginated list of published ads with minimal fields for map usage.
     * Filters are applied automatically via AdFilter (same as the public listing).
     */
    public function listPublishedAdsForMap(int $perPage = 50): LengthAwarePaginator
    {
        return $this->adRepository->paginatePublishedForMap($perPage);
    }

    // ─── Listing & Detail ─────────────────────────────────────────────────

    /**
     * Return a paginated list of published ads.
     * Filters are applied automatically via AdFilter (bound to the request).
     */
    public function listPublishedAds(int $perPage = 10): LengthAwarePaginator
    {
        return $this->adRepository->paginatePublished($perPage);
    }

    /**
     * Return a single published ad with full detail relations.
     * Records a view action for authenticated users (deduplicated per user per ad).
     * When a userId is provided, appends has_review and has_report flags to the model.
     *
     * @param  int|null  $userId  Pass the authenticated user's ID to record a view, or null for guests.
     *
     * @throws ModelNotFoundException
     */
    public function showPublishedAd(int $adId, ?int $userId = null): Ad
    {
        $ad = $this->adRepository->findPublishedWithDetails($adId, $userId);

        if ($ad === null) {
            throw new ModelNotFoundException(
                "Published ad #{$adId} not found."
            );
        }

        // Record view for authenticated users only (deduplicated)
        if ($userId !== null) {
            $this->adActionRepository->recordAction($adId, $userId, AdActionType::VIEW);
        }

        return $ad;
    }

    // ─── Reviews ─────────────────────────────────────────────────────────

    /**
     * Submit a review for an ad.
     * One review per user per ad — throws DomainException if already reviewed.
     *
     * @throws ModelNotFoundException Ad not found.
     * @throws \DomainException Already reviewed.
     */
    public function submitReview(int $adId, int $userId, int $rating, ?string $feedback): AdReview
    {
        // Ensure the ad exists and is published
        $this->showPublishedAd($adId);

        if ($this->reviewRepository->hasUserReviewedAd($adId, $userId)) {
            throw new \DomainException(
                __('You have already reviewed this ad.')
            );
        }

        /** @var AdReview $review */
        $review = $this->reviewRepository->create([
            'ad_id' => $adId,
            'user_id' => $userId,
            'rating' => $rating,
            'feedback' => $feedback,
        ]);

        return $review->load('user');
    }

    // ─── Reports ─────────────────────────────────────────────────────────

    /**
     * Submit a report for an ad.
     * One report per user per ad — throws DomainException if already reported.
     *
     * @throws ModelNotFoundException Ad not found.
     * @throws \DomainException Already reported.
     */
    public function submitReport(int $adId, int $userId, string $reason): AdReport
    {
        // Ensure the ad exists and is published
        $this->showPublishedAd($adId);

        if ($this->reportRepository->hasUserReportedAd($adId, $userId)) {
            throw new \DomainException(
                __('You have already reported this ad.')
            );
        }

        /** @var AdReport $report */
        $report = $this->reportRepository->create([
            'ad_id' => $adId,
            'user_id' => $userId,
            'reason' => $reason,
        ]);

        return $report;
    }
}
