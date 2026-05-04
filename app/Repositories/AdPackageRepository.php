<?php

namespace App\Repositories;

use App\Http\Filters\AdPackageFilter;
use App\Models\AdPackage;
use App\Repositories\Contracts\AdPackageRepositoryContract;
use App\Repositories\DTOs\QueryOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdPackageRepository extends BaseRepository implements AdPackageRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new AdPackage();
    }

    protected function resolveFilter(): AdPackageFilter
    {
        return app(AdPackageFilter::class);
    }

    /**
     * Return a paginated list of packages sorted by created_at DESC then
     * type DESC (offer > normal alphabetically), with filters applied.
     *
     * Uses the base get() method so filtering, pagination, and relations
     * are all handled by the existing infrastructure.
     */
    public function getAdPackages(int $perPage = 15): LengthAwarePaginator
    {
        return $this->get(QueryOptions::make([
            'perPage'       => $perPage,
            'applyFilters'  => true,
            'orderBy'       => 'created_at',
            'orderDirection'=> 'desc',
        ]));
    }

    /**
     * Check whether a package has any active (non-expired, non-cancelled) subscriptions.
     */
    public function hasActiveSubscriptions(int $id): bool
    {
        return $this->countActiveSubscriptions($id) > 0;
    }

    /**
     * Count active (non-expired, non-cancelled) subscriptions for a package.
     */
    public function countActiveSubscriptions(int $id): int
    {
        $package = $this->showOrFail($id, ['relations' => ['activeSubscriptions']]);

        return $package->activeSubscriptions->count();
    }
}
