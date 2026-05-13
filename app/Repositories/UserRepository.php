<?php

namespace App\Repositories;

use App\Http\Filters\BaseFilters;
use App\Http\Filters\UserFilter;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository implements UserRepositoryContract
{
    /**
     * Resolve the model instance.
     *
     * @return Model
     */
    protected function resolveModel(): Model
    {
        return new User();
    }

    /**
     * Resolve the filter instance.
     *
     * @return BaseFilters|null
     */
    protected function resolveFilter(): ?BaseFilters
    {
        return app(UserFilter::class) ?? null;
    }

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }

    public function getActiveUsers()
    {
        return $this->newQuery()
            ->where('status', 'active')
            ->get();
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function updateProfile(int $id, array $data): ?Model
    {
        return $this->update($id, $data);
    }

    /**
     * Count users who have at least one ad (advertisers).
     */
    public function countAdvertisers(): int
    {
        return $this->newQuery()
            ->whereHas('ads')
            ->count();
    }
}
