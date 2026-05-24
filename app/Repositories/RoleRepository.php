<?php

namespace App\Repositories;

use App\Http\Filters\RoleFilter;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class RoleRepository extends BaseRepository implements RoleRepositoryContract
{
    /**
     * Resolve the model instance.
     */
    protected function resolveModel(): Model
    {
        return new Role;
    }

    /**
     * Resolve the filter instance.
     */
    protected function resolveFilter(): ?RoleFilter
    {
        return new RoleFilter(request());
    }

    /**
     * Find a role that has exactly the same set of permissions.
     * Uses a subquery to match roles whose permission count equals the given
     * count AND all given permission IDs are assigned to that role.
     */
    public function findRoleWithSamePermissions(array $permissionIds, ?int $ignoreId = null): ?Model
    {
        sort($permissionIds);
        $count = count($permissionIds);

        $query = $this->newQuery()
            ->whereHas('permissions', function ($q) use ($permissionIds) {
                $q->whereIn('permissions.id', $permissionIds);
            }, '=', $count)
            ->withCount('permissions')
            ->having('permissions_count', '=', $count);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->first();
    }
}
