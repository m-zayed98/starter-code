<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdPackageRepositoryContract extends RepositoryContract
{
    /**
     * Return a paginated list of packages sorted by type DESC (offer first)
     * then by created_at DESC, with optional filters applied.
     */
    public function getAdPackages(int $perPage = 15): LengthAwarePaginator;

    /**
     * Return a paginated list of packages visible to clients (active, within
     * offer date range, not over subscriber cap), offers listed first.
     */
    public function getVisiblePackages(int $perPage = 15): LengthAwarePaginator;

    /**
     * Return a paginated list of packages visible to a specific subscriber.
     * Includes offer packages where the cap is reached but the user is already subscribed.
     */
    public function getVisiblePackagesForSubscriber(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Check whether a package has any active (non-expired, non-cancelled) subscriptions.
     */
    public function hasActiveSubscriptions(int $id): bool;

    /**
     * Count active (non-expired, non-cancelled) subscriptions for a package.
     */
    public function countActiveSubscriptions(int $id): int;
}
