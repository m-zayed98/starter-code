<?php

namespace App\Http\Filters;

class BlogFilter extends BaseFilters
{
    /**
     * Registered filters to operate upon.
     *
     * @var array
     */
    protected $filters = [
        'name',
        'status',
        'start_date',
        'end_date',
    ];

    /**
     * Filter by name (searches both Arabic and English names).
     *
     * @param string $value
     * @return void
     */
    protected function name(string $value): void
    {
        $this->builder->where(function ($query) use ($value) {
            $query->where('name->ar', 'like', "%{$value}%")
                  ->orWhere('name->en', 'like', "%{$value}%");
        });
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
     * Filter by start date (created_at >= start_date).
     *
     * @param string $value
     * @return void
     */
    protected function startDate(string $value): void
    {
        $this->builder->whereDate('created_at', '>=', $value);
    }

    /**
     * Filter by end date (created_at <= end_date).
     *
     * @param string $value
     * @return void
     */
    protected function endDate(string $value): void
    {
        $this->builder->whereDate('created_at', '<=', $value);
    }
}
