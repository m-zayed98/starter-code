<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use App\Facades\MediaUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/** @property UserRepositoryContract $repository */
class UserService extends BaseModelService
{
    public function __construct(UserRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get user profile by id.
     *
     * @param int $id
     * @return Model|null
     */
    public function getProfile(int $id): ?Model
    {
        return $this->repository->show($id);
    }

    /**
     * Update the authenticated user's profile.
     * Handles avatar upload via MediaUpload; all other logic in repository.
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function updateProfile(int $id, array $data): Model
    {
        return DB::transaction(function () use ($data, $id) {
            $avatarFile = Arr::pull($data, 'avatar');

            $user = $this->repository->updateProfile($id, $data);

            if ($avatarFile instanceof UploadedFile) {
                MediaUpload::file($avatarFile)
                    ->collection('avatar')
                    ->uploadTo($user);
            }

            return $user->refresh();
        });
    }
}
