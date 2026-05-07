<?php

namespace App\Repositories;

use App\Models\AdReport;
use App\Repositories\Contracts\AdReportRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class AdReportRepository extends BaseRepository implements AdReportRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new AdReport();
    }

    /**
     * Check whether a user has already reported a specific ad.
     */
    public function hasUserReportedAd(int $adId, int $userId): bool
    {
        return $this->newQuery()
            ->where('ad_id', $adId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Find a user's report for a specific ad.
     */
    public function findByAdAndUser(int $adId, int $userId): ?AdReport
    {
        /** @var AdReport|null */
        return $this->newQuery()
            ->where('ad_id', $adId)
            ->where('user_id', $userId)
            ->first();
    }
}
