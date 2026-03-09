<?php

namespace App\Services;

use App\Repositories\Contracts\RepositoryContract;
use App\Repositories\DTOs\QueryOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModelService
{
    protected RepositoryContract $repository;

    public function __construct(RepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function get(array|QueryOptions $options = []): Collection|LengthAwarePaginator
    {
        return $this->repository->get($options);
    }

    public function show(int $id, array|QueryOptions $options = []): ?Model
    {
        return $this->repository->show($id, $options);
    }

    public function showOrFail(int $id, array|QueryOptions $options = []): Model
    {
        return $this->repository->showOrFail($id, $options);
    }

    public function findBy(string $column, mixed $value, array|QueryOptions $options = []): ?Model
    {
        return $this->repository->findBy($column, $value, $options);
    }

    public function findAllBy(string $column, mixed $value, array|QueryOptions $options = []): Collection
    {
        return $this->repository->findAllBy($column, $value, $options);
    }

    public function create(array $data): Model
    {
        return $this->repository->create($this->prepareDataForCreate($data));
    }

    public function update(int $id, array $data): ?Model
    {
        $updated = $this->repository->update($id, $this->prepareDataForUpdate($data));

        if ($updated) {
            return $this->repository->show($id);
        }

        return null;
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->repository->updateOrCreate($attributes, $values);
    }

    public function delete(int $id, bool $forceDelete = false): bool
    {
        return $this->repository->delete($id, $forceDelete);
    }

    public function deleteMultiple(array $ids, bool $forceDelete = false): int
    {
        return $this->repository->deleteMultiple($ids, $forceDelete);
    }

    public function restore(int $id): bool
    {
        return $this->repository->restore($id);
    }

    public function count(bool $withTrashed = false): int
    {
        return $this->repository->count($withTrashed);
    }

    public function exists(int $id, bool $withTrashed = false): bool
    {
        return $this->repository->exists($id, $withTrashed);
    }

    protected function prepareDataForCreate(array $data): array
    {
        return $data;
    }

    protected function prepareDataForUpdate(array $data): array
    {
        return $data;
    }
}
