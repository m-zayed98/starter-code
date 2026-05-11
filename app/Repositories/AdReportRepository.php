<?php

namespace App\Repositories;

use App\Http\Filters\AdminAdReportFilter;
use App\Models\AdReport;
use App\Repositories\Contracts\AdReportRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdReportRepository extends BaseRepository implements AdReportRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new AdReport;
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

    /**
     * Return a paginated list of all reports for the admin panel.
     * Applies AdminAdReportFilter, eager-loads user and ad.
     */
    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        $filter = app(AdminAdReportFilter::class);

        return $this->newQuery()
            ->with(['user', 'ad'])
            ->tap(fn ($q) => $filter->apply($q))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a single report with full relations for the admin detail view.
     */
    public function findWithDetails(int $reportId): ?AdReport
    {
        /** @var AdReport|null */
        return $this->newQuery()
            ->where('id', $reportId)
            ->with(['user', 'ad.user'])
            ->first();
    }
}
