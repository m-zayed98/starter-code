<?php

namespace App\Services;

use App\Enums\AdStatus;
use App\Models\Ad;
use App\Repositories\Contracts\AdRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminAdService extends BaseModelService
{
    public function __construct(AdRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Return a paginated list of all ads for the admin panel.
     */
    public function listAds(int $perPage = 15): LengthAwarePaginator
    {
        /** @var AdRepositoryContract $repository */
        $repository = $this->repository;

        return $repository->paginateForAdmin($perPage);
    }

    /**
     * Return a single ad with full details for the admin detail view.
     *
     * @throws ModelNotFoundException
     */
    public function showAd(int $adId): Ad
    {
        /** @var AdRepositoryContract $repository */
        $repository = $this->repository;

        $ad = $repository->findWithFullDetails($adId);

        if ($ad === null) {
            throw new ModelNotFoundException(
                "Ad #{$adId} not found."
            );
        }

        return $ad;
    }

    /**
     * Toggle an ad between published and disabled (rejected) status.
     * - published  → rejected  (disabled / hidden from app)
     * - any other  → published (enabled / visible in app)
     *
     * @throws ModelNotFoundException
     */
    public function toggleStatus(int $adId): Ad
    {
        /** @var AdRepositoryContract $repository */
        $repository = $this->repository;

        $ad = $repository->findWithFullDetails($adId);

        if ($ad === null) {
            throw new ModelNotFoundException(
                "Ad #{$adId} not found."
            );
        }

        $newStatus = $ad->status === AdStatus::PUBLISHED
            ? AdStatus::REJECTED
            : AdStatus::PUBLISHED;

        $repository->update($adId, ['status' => $newStatus->value]);

        return $repository->findWithFullDetails($adId);
    }
}
