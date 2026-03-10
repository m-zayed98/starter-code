<?php

namespace App\Repositories\Contracts;

use App\Repositories\DTOs\QueryOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryContract
{
    public function get(array|QueryOptions $options = []): Collection|LengthAwarePaginator;

    public function show(int $id, array|QueryOptions $options = []): ?Model;

    public function showOrFail(int $id, array|QueryOptions $options = []): Model;

    public function findBy(string $column, mixed $value, array|QueryOptions $options = []): ?Model;

    public function findAllBy(string $column, mixed $value, array|QueryOptions $options = []): Collection;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function updateOrCreate(array $attributes, array $values = []): Model;

    public function delete(int $id, bool $forceDelete = false): bool;

    public function deleteMultiple(array $ids, bool $forceDelete = false): int;

    public function restore(int $id): bool;

    public function count(bool $withTrashed = false): int;

    public function exists(int $id, bool $withTrashed = false): bool;
}
