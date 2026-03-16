<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryContract extends RepositoryContract
{
    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get active users.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveUsers();

    /**
     * Update the authenticated user's profile.
     *
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function updateProfile(int $id, array $data): ?Model;
}
