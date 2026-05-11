<?php

namespace App\Services;

use App\Models\AdReview;
use App\Repositories\Contracts\AdReviewRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminAdReviewService extends BaseModelService
{
    public function __construct(AdReviewRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Return a paginated list of all reviews for the admin panel.
     */
    public function listReviews(int $perPage = 15): LengthAwarePaginator
    {
        /** @var AdReviewRepositoryContract $repository */
        $repository = $this->repository;

        return $repository->paginateForAdmin($perPage);
    }

    /**
     * Toggle review visibility (publish/hide).
     *
     * @throws ModelNotFoundException
     */
    public function toggleVisibility(int $reviewId): AdReview
    {
        /** @var AdReviewRepositoryContract $repository */
        $repository = $this->repository;

        $review = $repository->showOrFail($reviewId);

        $repository->update($reviewId, [
            'is_visible' => ! $review->is_visible,
        ]);

        return $repository->showOrFail($reviewId);
    }
}
