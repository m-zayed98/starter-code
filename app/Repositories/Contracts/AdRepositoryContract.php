<?php

namespace App\Repositories\Contracts;

use App\Models\Ad;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdRepositoryContract extends RepositoryContract
{
    /**
     * Check whether an ad with the given ad_license_number already exists.
     */
    public function existsByAdLicenseNumber(string $adLicenseNumber): bool;

    /**
     * Find an ad by its ad_license_number.
     */
    public function findByAdLicenseNumber(string $adLicenseNumber): ?Ad;

    /**
     * Return a paginated list of published ads for the public listing.
     * Applies AdFilter, eager-loads media.
     */
    public function paginatePublished(int $perPage = 10): LengthAwarePaginator;

    /**
     * Find a single published ad by ID with full relations for detail view.
     */
    public function findPublishedWithDetails(int $adId): ?Ad;

    /**
     * Return a paginated list of ads belonging to a specific user.
     */
    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a specific ad that belongs to a given user (ownership check).
     */
    public function findForUser(int $adId, int $userId): ?Ad;

    /**
     * Return a paginated list of all ads for the admin panel.
     * Applies AdminAdFilter, eager-loads user and media.
     */
    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a single ad by ID with full relations for the admin detail view.
     * Eager-loads user, media, reviews (with user), and reports count.
     */
    public function findWithFullDetails(int $adId): ?Ad;

    /**
     * Count published (active) ads.
     */
    public function countActive(): int;

    /**
     * Count non-published (inactive) ads.
     */
    public function countInactive(): int;
}
