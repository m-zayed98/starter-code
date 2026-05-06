<?php

namespace App\Repositories;

use App\Http\Filters\BlogFilter;
use App\Models\Blog;
use App\Repositories\Contracts\BlogRepositoryContract;
use App\Repositories\DTOs\QueryOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class BlogRepository extends BaseRepository implements BlogRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new Blog();
    }

    protected function resolveFilter(): ?BlogFilter
    {
        return app(BlogFilter::class);
    }

    /**
     * Get paginated blogs with optional filters applied.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getBlogs(int $perPage = 15): LengthAwarePaginator
    {
        return $this->get(QueryOptions::make([
            'perPage'        => $perPage,
            'applyFilters'   => true,
            'orderBy'        => 'created_at',
            'orderDirection' => 'desc',
            'relations'      => ['comments', 'media'],
        ]));
    }

    /**
     * Search blogs by name (searches both Arabic and English).
     *
     * @param string $searchTerm
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchByName(string $searchTerm, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where(function ($q) use ($searchTerm) {
                $q->where('name->ar', 'like', "%{$searchTerm}%")
                  ->orWhere('name->en', 'like', "%{$searchTerm}%");
            })
            ->with(['comments', 'media'])
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Filter blogs by date range.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function filterByDateRange(?string $startDate, ?string $endDate, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->with(['comments', 'media']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get active blogs for landing page display.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveBlogs(int $perPage = 10): LengthAwarePaginator
    {
        return $this->newQuery()
            ->active()
            ->newestFirst()
            ->with(['comments', 'media'])
            ->paginate($perPage);
    }

    /**
     * Get blog with comments.
     *
     * @param int $id
     * @return Model
     */
    public function getBlogWithComments(int $id): Model
    {
        return $this->showOrFail($id, QueryOptions::make([
            'relations' => ['comments', 'media'],
        ]));
    }

    /**
     * Delete blog and associated media.
     *
     * @param int $id
     * @return bool
     */
    public function deleteWithMedia(int $id): bool
    {
        $blog = $this->showOrFail($id);
        
        // Clear all media using Spatie Media Library
        $blog->clearMediaCollection('main_image_ar');
        $blog->clearMediaCollection('main_image_en');
        
        return $blog->delete();
    }
}
