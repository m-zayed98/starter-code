<?php

namespace App\Http\Filters;

/**
 * AdminAdReportFilter
 *
 * Filters for the admin ad reports listing:
 *   search  – user name or user phone (joined via user relation)
 *   ad_name – ad title
 *   status  – pending | replied
 */
class AdminAdReportFilter extends BaseFilters
{
    protected $filters = [
        'search',
        'ad_name',
        'status',
    ];

    /**
     * Search by user name or user phone.
     * Requires the query to have joined/loaded the users table.
     */
    protected function search(string $value): void
    {
        $term = "%{$value}%";

        $this->builder->whereHas('user', function ($q) use ($term) {
            $q->where('name', 'like', $term)
                ->orWhere('phone', 'like', $term);
        });
    }

    /**
     * Filter by ad title.
     */
    protected function adName(string $value): void
    {
        $term = "%{$value}%";

        $this->builder->whereHas('ad', function ($q) use ($term) {
            $q->where('title', 'like', $term);
        });
    }

    /**
     * Filter by report status: pending | replied
     */
    protected function status(string $value): void
    {
        $this->builder->where('status', $value);
    }
}
