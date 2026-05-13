<?php

namespace App\Services;

use App\Enums\AdActionType;
use App\Repositories\Contracts\AdActionRepositoryContract;
use App\Repositories\Contracts\AdRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;

class AdActionService
{
    public function __construct(
        private readonly AdActionRepositoryContract $adActionRepository,
        private readonly AdRepositoryContract       $adRepository,
        private readonly UserRepositoryContract     $userRepository,
    ) {}

    /**
     * Record a view action for an ad.
     *
     * Called automatically when an authenticated user opens the ad detail page.
     * Guests are not tracked (no user_id to deduplicate on).
     */
    public function recordView(int $adId, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }

        $this->adActionRepository->recordAction($adId, $userId, AdActionType::VIEW);
    }

    /**
     * Record a call or whatsapp action for an ad.
     *
     * Called via POST /public/ads/{id}/action.
     * Requires authentication. Deduplicated per user per ad per type.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  Ad not found or not published.
     * @throws \InvalidArgumentException                              View type not allowed here.
     */
    public function recordAction(int $adId, int $userId, AdActionType $type): void
    {
        if ($type === AdActionType::VIEW) {
            throw new \InvalidArgumentException(
                'Use recordView() to record view actions.'
            );
        }

        $ad = $this->adRepository->findPublishedWithDetails($adId);

        if ($ad === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Published ad #{$adId} not found."
            );
        }

        $this->adActionRepository->recordAction($adId, $userId, $type);
    }

    // ─── Admin Statistics ─────────────────────────────────────────────────

    /**
     * Return dashboard statistics for the admin panel.
     *
     * - active_ads_count       : published ads
     * - inactive_ads_count     : non-published ads
     * - total_users_count      : all registered users
     * - advertiser_users_count : users with at least one ad
     *
     * @return array<string, int>
     */
    public function getAdminStats(): array
    {
        $activeAds       = $this->adRepository->countActive();
        $inactiveAds     = $this->adRepository->countInactive();
        $totalUsers      = $this->userRepository->count();
        $advertiserUsers = $this->userRepository->countAdvertisers();

        return [
            'active_ads_count'       => $activeAds,
            'inactive_ads_count'     => $inactiveAds,
            'total_users_count'      => $totalUsers,
            'advertiser_users_count' => $advertiserUsers,
        ];
    }
}
