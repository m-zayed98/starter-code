<?php

namespace App\Repositories;

use App\Models\Ad;
use App\Repositories\Contracts\AdRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdRepository extends BaseRepository implements AdRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new Ad();
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
     * Return a paginated list of ads belonging to a specific user.
     * Eager-loads media so the resource can render images without extra queries.
     */
    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->with('media')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a specific ad that belongs to a given user (ownership check).
     * Eager-loads media for resource rendering.
     */
    public function findForUser(int $adId, int $userId): ?Ad
    {
        /** @var Ad|null */
        return $this->newQuery()
            ->where('id', $adId)
            ->where('user_id', $userId)
            ->with('media')
            ->first();
    }
}
