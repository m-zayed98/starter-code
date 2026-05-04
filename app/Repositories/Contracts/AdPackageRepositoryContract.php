<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AdPackageRepositoryContract extends RepositoryContract
{
    /**
     * Return a paginated list of packages sorted by type DESC (offer first)
     * then by created_at DESC, with optional filters applied.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAdPackages(int $perPage = 15): LengthAwarePaginator;

    /**
     * Check whether a package has any active (non-expired, non-cancelled) subscriptions.
     *
     * @param int $id
     * @return bool
     */
    public function hasActiveSubscriptions(int $id): bool;

    /**
     * Count active (non-expired, non-cancelled) subscriptions for a package.
     *
     * @param int $id
     * @return int
     */
    public function countActiveSubscriptions(int $id): int;
}
