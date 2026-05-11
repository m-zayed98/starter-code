<?php

namespace App\Http\Filters;

/**
 * AdminAdFilter
 *
 * Filters available for the admin ads listing:
 *   search  – matches ad title or advertiser name (from nhc_data)
 *   status  – filter by ad status (draft | published | expired | rejected)
 */
class AdminAdFilter extends BaseFilters
{
    protected $filters = [
        'search',
        'status',
    ];

    /**
     * Full-text search across ad title and advertiser name stored in nhc_data.
     */
    protected function search(string $value): void
    {
        $term = "%{$value}%";

        $this->builder->where(function ($query) use ($term) {
            $query->where('title', 'like', $term)
                ->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.advertiser_name')) LIKE ?",
                    [$term]
                );
        });
    }

    /**
     * Filter by ad status: draft | published | expired | rejected
     */
    protected function status(string $value): void
    {
        $this->builder->where('status', $value);
    }
}
