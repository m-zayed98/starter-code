<?php

namespace App\Repositories\Contracts;

use App\Models\User;

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
}
