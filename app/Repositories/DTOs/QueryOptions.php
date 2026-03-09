<?php

namespace App\Repositories\DTOs;

class QueryOptions
{
    public function __construct(
        public readonly array $columns = ['*'],
        public readonly array $relations = [],
        public readonly bool $withTrashed = false,
        public readonly bool $applyFilters = false,
        public readonly ?int $perPage = null,
        public readonly string $orderBy = 'created_at',
        public readonly string $orderDirection = 'desc',
    ) {
    }

    public static function make(array $options = []): self
    {
        return new self(
            columns: $options['columns'] ?? ['*'],
            relations: $options['relations'] ?? [],
            withTrashed: $options['withTrashed'] ?? false,
            applyFilters: $options['applyFilters'] ?? false,
            perPage: $options['perPage'] ?? null,
            orderBy: $options['orderBy'] ?? 'created_at',
            orderDirection: $options['orderDirection'] ?? 'desc',
        );
    }

    public function isPaginated(): bool
    {
        return $this->perPage !== null;
    }

    public function toArray(): array
    {
        return [
            'columns' => $this->columns,
            'relations' => $this->relations,
            'withTrashed' => $this->withTrashed,
            'applyFilters' => $this->applyFilters,
            'perPage' => $this->perPage,
            'orderBy' => $this->orderBy,
            'orderDirection' => $this->orderDirection,
        ];
    }
}
