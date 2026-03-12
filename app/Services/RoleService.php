<?php

namespace App\Services;

use App\Models\Permission;
use App\Repositories\Contracts\RoleRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RoleService extends BaseModelService
{
    public function __construct(RoleRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        $created = DB::transaction(function () use ($data) {
            $permissions = Arr::pull($data, 'permissions');
            $data['guard_name'] = 'admin';
            $created = $this->repository->create($data);
            $permissions = Permission::query()->whereIn('id', $permissions)->get();
            $created->syncPermissions($permissions);
            return $created;
        });
        return $created;
    }

    public function update(int $id, array $data): ?Model
    {
        DB::transaction(function () use ($data, $id) {
            $permissions = Arr::pull($data, 'permissions');
            $data['guard_name'] = 'admin';
            $this->repository->showOrFail($id);
            $updated = $this->repository->update($id, $data);
            if ($permissions) {
                $permissions = Permission::query()->whereIn('id', $permissions)->get();
                $updated->syncPermissions($permissions);
            }
            return $updated;
        });
        return $this->repository->show($id);
    }
}
