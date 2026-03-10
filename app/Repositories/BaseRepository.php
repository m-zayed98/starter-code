<?php

namespace App\Repositories;

use App\Http\Filters\BaseFilters;
use App\Repositories\Contracts\RepositoryContract;
use App\Repositories\DTOs\QueryOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryContract
{
    protected Model $model;
    protected ?BaseFilters $filter;

    public function __construct()
    {
        $this->model = $this->resolveModel();
        $this->filter = $this->resolveFilter();
    }

    abstract protected function resolveModel(): Model;

    protected function resolveFilter(): ?BaseFilters
    {
        return null;
    }

    protected function newQuery(bool $withTrashed = false): Builder
    {
        $query = $this->model->newQuery();

        if ($withTrashed && method_exists($this->model, 'trashed')) {
            $query->withTrashed();
        }

        return $query;
    }

    protected function applyFilters(Builder $query): Builder
    {
        if ($this->filter) {
            return $this->filter->apply($query);
        }

        return $query;
    }

    protected function normalizeOptions(array|QueryOptions $options): QueryOptions
    {
        return $options instanceof QueryOptions
            ? $options
            : QueryOptions::make($options);
    }

    protected function buildQuery(QueryOptions $options): Builder
    {
        $query = $this->newQuery($options->withTrashed);

        if (!empty($options->relations)) {
            $query->with($options->relations);
        }

        if ($options->applyFilters) {
            $query = $this->applyFilters($query);
        }

        $query->orderBy($options->orderBy, $options->orderDirection);

        return $query;
    }

    public function get(array|QueryOptions $options = []): Collection|LengthAwarePaginator
    {
        $options = $this->normalizeOptions($options);
        $query = $this->buildQuery($options);

        return $options->isPaginated()
            ? $query->paginate($options->perPage, $options->columns)
            : $query->get($options->columns);
    }

    public function show(int $id, array|QueryOptions $options = []): ?Model
    {
        $options = $this->normalizeOptions($options);
        $query = $this->newQuery($options->withTrashed);

        if (!empty($options->relations)) {
            $query->with($options->relations);
        }

        return $query->find($id, $options->columns);
    }

    public function showOrFail(int $id, array|QueryOptions $options = []): Model
    {
        $options = $this->normalizeOptions($options);
        $query = $this->newQuery($options->withTrashed);

        if (!empty($options->relations)) {
            $query->with($options->relations);
        }

        return $query->findOrFail($id, $options->columns);
    }

    public function findBy(string $column, mixed $value, array|QueryOptions $options = []): ?Model
    {
        $options = $this->normalizeOptions($options);
        $query = $this->newQuery($options->withTrashed);

        if (!empty($options->relations)) {
            $query->with($options->relations);
        }

        return $query->where($column, $value)->first($options->columns);
    }

    public function findAllBy(string $column, mixed $value, array|QueryOptions $options = []): Collection
    {
        $options = $this->normalizeOptions($options);
        $query = $this->buildQuery($options);

        return $query->where($column, $value)->get($options->columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data)->refresh();
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->show($id);
        $record->update($data);
        return $record->refresh();
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function delete(int $id, bool $forceDelete = false): bool
    {
        $record = $this->show($id, ['withTrashed' => $forceDelete]);

        if ($record) {
            if ($forceDelete && method_exists($record, 'forceDelete')) {
                return $record->forceDelete();
            }

            return $record->delete();
        }

        return false;
    }

    public function deleteMultiple(array $ids, bool $forceDelete = false): int
    {
        $query = $this->newQuery();

        if ($forceDelete && method_exists($this->model, 'forceDelete')) {
            return $query->whereIn('id', $ids)->forceDelete();
        }

        return $query->whereIn('id', $ids)->delete();
    }

    public function restore(int $id): bool
    {
        $record = $this->show($id, ['withTrashed' => true]);

        if ($record && method_exists($record, 'restore')) {
            return $record->restore();
        }

        return false;
    }

    public function count(bool $withTrashed = false): int
    {
        return $this->newQuery($withTrashed)->count();
    }

    public function exists(int $id, bool $withTrashed = false): bool
    {
        return $this->newQuery($withTrashed)->where('id', $id)->exists();
    }
}
