<?php

namespace App\Repositories\Contracts;

use App\Models\AdReport;

interface AdReportRepositoryContract extends RepositoryContract
{
    /**
     * Check whether a user has already reported a specific ad.
     */
    public function hasUserReportedAd(int $adId, int $userId): bool;

    /**
     * Find a user's report for a specific ad.
     */
    public function findByAdAndUser(int $adId, int $userId): ?AdReport;
}
