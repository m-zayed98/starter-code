<?php

namespace App\Repositories\Contracts;

interface AdminRepositoryContract extends RepositoryContract
{
    /**
     * Update the authenticated admin's profile.
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function updateProfile(int $id, array $data): ?\Illuminate\Database\Eloquent\Model;
}
