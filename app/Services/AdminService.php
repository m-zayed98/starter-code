<?php

namespace App\Services;

use App\Repositories\Contracts\AdminRepositoryContract;
use App\Repositories\Contracts\RepositoryContract;
use App\Repositories\Contracts\RoleRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use App\Facades\MediaUpload;
use Illuminate\Support\Arr;

class AdminService extends BaseModelService
{
    /**
     * Create a new service instance.
     *
     * @param AdminRepositoryContract $repository
     */
    public function __construct(AdminRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        $admin = \DB::transaction(function () use ($data) {
            $roleId = Arr::pull($data, 'role_id');
            $avatarFile = Arr::pull($data, 'avatar');;

            $admin = $this->repository->create($data);
            if ($avatarFile instanceof UploadedFile) {
                MediaUpload::file($avatarFile)
                    ->collection('avatar')
                    ->uploadTo($admin);
            }

            if ($roleId) {
                $role = app(RoleRepositoryContract::class)->show($roleId);
                $admin->assignRole($role);
            }
            return $admin;
        });


        return $admin->refresh();
    }

    public function update(int $id, array $data): ?Model
    {
        $admin = \DB::transaction(function () use ($data, $id) {
            $roleId = Arr::pull($data, 'role_id');
            $avatarFile = Arr::pull($data, 'avatar');;

            $admin = $this->repository->update($id, $data);
            if ($avatarFile instanceof UploadedFile) {
                MediaUpload::file($avatarFile)
                    ->collection('avatar')
                    ->uploadTo($admin);
            }

            if ($roleId) {
                $role = app(RoleRepositoryContract::class)->show($roleId);
                $admin->syncRoles($role);
            }
            return $admin;
        });

        return $admin;
    }
}