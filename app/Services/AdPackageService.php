<?php

namespace App\Services;

use App\Facades\MediaUpload;
use App\Repositories\Contracts\AdPackageRepositoryContract;
use App\Repositories\DTOs\QueryOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

/** @property AdPackageRepositoryContract $repository */
class AdPackageService extends BaseModelService
{
    public function __construct(AdPackageRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Always eager-load activeSubscriptions so AdPackageDetailResource
     * can call ->activeSubscriptions->count() on every item.
     */
    public function get(array|QueryOptions $options = []): Collection|LengthAwarePaginator
    {
        $options = $options instanceof QueryOptions ? $options : QueryOptions::make($options);

        $merged = QueryOptions::make(array_merge($options->toArray(), [
            'relations' => array_unique(array_merge($options->relations, ['activeSubscriptions'])),
        ]));

        return $this->repository->get($merged);
    }

    /**
     * Create a package with optional image upload.
     * Returns the package with activeSubscriptions loaded.
     */
    public function create(array $data): Model
    {
        // Extract the image file before persisting (not a DB column)
        $imageFile = $data['image'] ?? null;
        unset($data['image']);

        $package = $this->repository->create($data);

        // Upload image if provided
        if ($imageFile instanceof UploadedFile) {
            MediaUpload::file($imageFile)
                ->collection('image')
                ->uploadTo($package);
        }

        return $this->repository->showOrFail($package->id, ['relations' => ['activeSubscriptions']]);
    }

    /**
     * Update a package with optional image replacement.
     * Returns the package with activeSubscriptions loaded.
     */
    public function update(int $id, array $data): Model
    {
        // Extract the image file before persisting (not a DB column)
        $imageFile = $data['image'] ?? null;
        unset($data['image']);

        $package = $this->repository->update($id, $data);

        // Replace image if a new one is provided
        if ($imageFile instanceof UploadedFile) {
            $package->clearMediaCollection('image');
            MediaUpload::file($imageFile)
                ->collection('image')
                ->uploadTo($package);
        }

        return $this->repository->showOrFail($id, ['relations' => ['activeSubscriptions']]);
    }

    /**
     * Return a package with activeSubscriptions loaded.
     */
    public function showOrFail(int $id, array|QueryOptions $options = []): Model
    {
        $options = $options instanceof QueryOptions ? $options : QueryOptions::make($options);

        $merged = QueryOptions::make(array_merge($options->toArray(), [
            'relations' => array_unique(array_merge($options->relations, ['activeSubscriptions'])),
        ]));

        return $this->repository->showOrFail($id, $merged);
    }

    /**
     * Toggle is_active. Existing subscriptions are not affected.
     */
    public function toggle(int $id): Model
    {
        $package = $this->repository->showOrFail($id);

        return $this->repository->update($id, ['is_active' => ! $package->is_active]);
    }

    /**
     * Delete a package only if it has no active subscriptions.
     *
     * @throws \DomainException
     */
    public function delete(int $id, bool $forceDelete = false): bool
    {
        $this->repository->showOrFail($id);

        if ($this->repository->hasActiveSubscriptions($id)) {
            throw new \DomainException(
                __('This package has active subscriptions and cannot be deleted.')
            );
        }

        return $this->repository->delete($id, $forceDelete);
    }
}
