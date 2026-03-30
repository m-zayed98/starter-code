<?php

namespace App\Http\Filters;

class RoleFilter extends BaseFilters
{
    protected $filters = [
        'search',
        'status',
    ];

    protected function search(string $value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    protected function status($value)
    {
        $status = strtolower($value) == 'active' ? true : false;
        $this->builder->where('is_active', $status);
    }
}
