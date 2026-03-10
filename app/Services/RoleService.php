<?php

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class RoleService extends BaseModelService
{
    public function __construct(RoleRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        $permissions = Arr::pull($data, 'permissions');
        $created = $this->repository->create($data);

        $created->syncPermissions($permissions);

        return $created;
    }

    public function update(int $id, array $data): ?Model
    {
        $permissions = Arr::pull($data, 'permissions');
        $updated = $this->repository->update($id, $data);
        $updated->syncPermissions($permissions);

        return $this->repository->show($id);
    }
}