<?php

namespace App\Repositories;

use App\Http\Filters\AdFilter;
use App\Http\Filters\AdminAdFilter;
use App\Http\Filters\BaseFilters;
use App\Models\Ad;
use App\Repositories\Contracts\AdRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdRepository extends BaseRepository implements AdRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new Ad;
    }

    protected function resolveFilter(): ?BaseFilters
    {
        return app(AdFilter::class);
    }

    /**
     * Check whether an ad with the given ad_license_number already exists.
     */
    public function existsByAdLicenseNumber(string $adLicenseNumber): bool
    {
        return $this->newQuery()
            ->where('ad_license_number', $adLicenseNumber)
            ->exists();
    }

    /**
     * Find an ad by its ad_license_number.
     */
    public function findByAdLicenseNumber(string $adLicenseNumber): ?Ad
    {
        /** @var Ad|null */
        return $this->findBy('ad_license_number', $adLicenseNumber);
    }

    /**
     * Return a paginated list of published ads for the public listing.
     * Applies AdFilter, eager-loads media and per-ad action counts + review aggregates.
     */
    public function paginatePublished(int $perPage = 10): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('status', 'published')
            ->with('media')
            ->withCount([
                'actions as views_count'    => fn($q) => $q->where('type', 'view'),
                'actions as calls_count'    => fn($q) => $q->where('type', 'call'),
                'actions as whatsapp_count' => fn($q) => $q->where('type', 'whatsapp'),
                'reviews as reviews_count',
            ])
            ->withAvg('reviews as average_rating', 'rating')
            ->tap(fn($q) => $this->applyFilters($q))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a single published ad by ID with full relations for detail view.
     * Eager-loads user, media, reviews (with user), reports count, and action counts.
     * When $userId is provided, also appends has_review and has_report counts scoped to that user.
     */
    public function findPublishedWithDetails(int $adId, ?int $userId = null): ?Ad
    {
        /** @var Ad|null */
        return $this->newQuery()
            ->where('id', $adId)
            ->where('status', 'published')
            ->with(['user', 'media', 'reviews.user'])
            ->withCount([
                'reports',
                'reviews as reviews_count',
                'actions as views_count'    => fn($q) => $q->where('type', 'view'),
                'actions as calls_count'    => fn($q) => $q->where('type', 'call'),
                'actions as whatsapp_count' => fn($q) => $q->where('type', 'whatsapp'),
                ...($userId !== null ? [
                    'reviews as has_review' => fn($q) => $q->where('user_id', $userId),
                    'reports as has_report' => fn($q) => $q->where('user_id', $userId),
                ] : []),
            ])
            ->withAvg('reviews as average_rating', 'rating')
            ->first();
    }

    /**
     * Return a paginated list of ads belonging to a specific user.
     * Applies AdFilter (same filters as the public listing), eager-loads media and per-ad action counts + review aggregates.
     */
    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->with('media')
            ->withCount([
                'actions as views_count'    => fn($q) => $q->where('type', 'view'),
                'actions as calls_count'    => fn($q) => $q->where('type', 'call'),
                'actions as whatsapp_count' => fn($q) => $q->where('type', 'whatsapp'),
                'reviews as reviews_count',
            ])
            ->withAvg('reviews as average_rating', 'rating')
            ->tap(fn($q) => $this->applyFilters($q))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a specific ad that belongs to a given user (ownership check).
     * Eager-loads media and per-ad action counts + review aggregates.
     */
    public function findForUser(int $adId, int $userId): ?Ad
    {
        /** @var Ad|null */
        return $this->newQuery()
            ->where('id', $adId)
            ->where('user_id', $userId)
            ->with('media')
            ->withCount([
                'actions as views_count'    => fn($q) => $q->where('type', 'view'),
                'actions as calls_count'    => fn($q) => $q->where('type', 'call'),
                'actions as whatsapp_count' => fn($q) => $q->where('type', 'whatsapp'),
                'reviews as reviews_count',
            ])
            ->withAvg('reviews as average_rating', 'rating')
            ->first();
    }

    /**
     * Return a paginated list of all ads for the admin panel.
     * Applies AdminAdFilter, eager-loads user and media.
     */
    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        $adminFilter = app(AdminAdFilter::class);

        return $this->newQuery()
            ->with(['user', 'media'])
            ->tap(fn($q) => $adminFilter->apply($q))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a single ad by ID with full relations for the admin detail view.
     * Eager-loads user, media, reviews (with user), and reports count.
     */
    public function findWithFullDetails(int $adId): ?Ad
    {
        /** @var Ad|null */
        return $this->newQuery()
            ->where('id', $adId)
            ->with(['user', 'media', 'reviews.user'])
            ->withCount('reports')
            ->first();
    }

    /**
     * Return a paginated list of published ads with minimal fields for map usage.
     * Applies AdFilter, eager-loads only cover_image media.
     */
    public function paginatePublishedForMap(int $perPage = 50): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('status', 'published')
            ->with('media')
            ->tap(fn($q) => $this->applyFilters($q))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Count published (active) ads.
     */
    public function countActive(): int
    {
        return $this->newQuery()
            ->where('status', 'published')
            ->count();
    }

    /**
     * Count non-published (inactive) ads.
     */
    public function countInactive(): int
    {
        return $this->newQuery()
            ->where('status', '!=', 'published')
            ->count();
    }

    /**
     * Count published ads for a specific user.
     */
    public function countPublishedForUser(int $userId): int
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('status', 'published')
            ->count();
    }

    /**
     * Count unpublished (non-published) ads for a specific user.
     */
    public function countUnpublishedForUser(int $userId): int
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('status', '!=', 'published')
            ->count();
    }

    /**
     * Sum total views across all ads for a specific user.
     */
    public function sumViewsForUser(int $userId): int
    {
        return (int) $this->newQuery()
            ->where('ads.user_id', $userId)
            ->join('ad_actions', 'ads.id', '=', 'ad_actions.ad_id')
            ->where('ad_actions.type', 'view')
            ->count('ad_actions.id');
    }
}
