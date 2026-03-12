<?php

namespace App\Repositories;

use App\Http\Filters\AdminFilter;
use App\Models\Admin;
use App\Repositories\Contracts\AdminRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class AdminRepository extends BaseRepository implements AdminRepositoryContract
{
    /**
     * Resolve the model instance.
     *
     * @return Model
     */
    protected function resolveModel(): Model
    {
        return new Admin();
    }

    /**
     * Resolve the filter instance.
     *
     * @return AdminFilter|null
     */
    protected function resolveFilter(): ?AdminFilter
    {
        return new AdminFilter(request());
    }

    /**
     * Update the authenticated admin's profile.
     *
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function updateProfile(int $id, array $data): ?Model
    {
        return $this->update($id, $data);
    }
}
