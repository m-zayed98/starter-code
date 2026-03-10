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
     *
     * @return Model
     */
    protected function resolveModel(): Model
    {
        return new Role();
    }

    /**
     * Resolve the filter instance.
     *
     * @return RoleFilter|null
     */
    protected function resolveFilter(): ?RoleFilter
    {
        return new RoleFilter(request());
    }
}