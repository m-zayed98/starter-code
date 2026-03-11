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
        // Add more filters here
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
            // Add more searchable fields here
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
        $this->builder->where('status', $value);
    }
}