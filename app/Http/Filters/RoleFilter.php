<?php

namespace App\Http\Filters;

class RoleFilter extends BaseFilters
{
    protected $filters = [
        'search',
        'is_active',
    ];

    protected function search(string $value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    protected function isActive(string $value): void
    {
        $this->builder->where('is_active', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }
}