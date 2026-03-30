<?php

namespace App\Http\Filters;

class AdminFilter extends BaseFilters
{
    /**
     * Registered filters to operate upon.
     *
     * @var array
     */
    protected $filters = [
        'search',
        'status',
    ];

    /**
     * Filter by search term.
     *
     * @param string $value
     * @return void
     */
    protected function search($value)
    {
        $this->builder->where(function ($query) use ($value) {
            $query->where('name', 'like', "%{$value}%");
        });
    }

    /**
     * Filter by status.
     *
     * @param string $value
     * @return void
     */
    protected function status($value)
    {
        $status = $value === 'active' ? true : false;
        $this->builder->where('is_active', $status);
    }
}
