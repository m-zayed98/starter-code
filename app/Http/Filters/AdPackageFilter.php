<?php

namespace App\Http\Filters;

class AdPackageFilter extends BaseFilters
{
    /**
     * Registered filters to operate upon.
     *
     * @var array
     */
    protected $filters = [
        'name',
        'status',
        'type',
    ];

    /**
     * Filter by name (partial match).
     *
     * @param string $value
     * @return void
     */
    protected function name(string $value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    /**
     * Filter by active status.
     *
     * @param string $value
     * @return void
     */
    protected function status(string $value): void
    {
        $this->builder->where('is_active', $value);
    }

    /**
     * Filter by package type (normal / offer).
     *
     * @param string $value
     * @return void
     */
    protected function type(string $value): void
    {
        $this->builder->where('type', $value);
    }
}
