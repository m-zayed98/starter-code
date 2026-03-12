<?php

namespace App\Services;

use App\Repositories\Contracts\AdminRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use App\Facades\MediaUpload;

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
        $roleId = $data['role_id'] ?? null;
        $avatarFile = $data['avatar'] ?? null;
        unset($data['role_id'], $data['avatar']);

        /** @var \App\Models\Admin&Model $admin */
        $admin = $this->repository->create($data);

        if ($avatarFile instanceof UploadedFile) {
            MediaUpload::file($avatarFile)
                ->collection('avatar')
                ->uploadTo($admin);
        }

        if ($roleId !== null) {
            $admin->syncRoles([$roleId]);
        }

        return $admin->refresh();
    }

    public function update(int $id, array $data): ?Model
    {
        $roleId = $data['role_id'] ?? null;
        $avatarFile = $data['avatar'] ?? null;
        unset($data['role_id'], $data['avatar']);

        $updated = $this->repository->update($id, $data);

        if (! $updated) {
            return null;
        }

        /** @var \App\Models\Admin&Model|null $admin */
        $admin = $this->repository->show($id);

        if ($admin) {
            if ($avatarFile instanceof UploadedFile) {
                $admin->clearMediaCollection('avatar');

                MediaUpload::file($avatarFile)
                    ->collection('avatar')
                    ->uploadTo($admin);
            }

            if ($roleId !== null) {
                $admin->syncRoles([$roleId]);
            }
        }

        return $admin;
    }
}