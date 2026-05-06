<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface BlogRepositoryContract extends RepositoryContract
{
    /**
     * Get paginated blogs with optional filters applied.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getBlogs(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search blogs by name (searches both Arabic and English).
     *
     * @param string $searchTerm
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchByName(string $searchTerm, int $perPage = 15): LengthAwarePaginator;

    /**
     * Filter blogs by date range.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function filterByDateRange(?string $startDate, ?string $endDate, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active blogs for landing page display.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveBlogs(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get blog with comments.
     *
     * @param int $id
     * @return Model
     */
    public function getBlogWithComments(int $id): Model;

    /**
     * Delete blog and associated media.
     *
     * @param int $id
     * @return bool
     */
    public function deleteWithMedia(int $id): bool;
}
