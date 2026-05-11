<?php

namespace App\Repositories\Contracts;

use App\Models\AdReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    /**
     * Return a paginated list of all reports for the admin panel.
     * Applies AdminAdReportFilter, eager-loads user and ad.
     */
    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a single report with full relations for the admin detail view.
     */
    public function findWithDetails(int $reportId): ?AdReport;
}
