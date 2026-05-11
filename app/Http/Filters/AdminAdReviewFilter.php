<?php

namespace App\Http\Filters;

/**
 * AdminAdReviewFilter
 *
 * Filters for the admin ad reviews listing:
 *   search  – user name or user phone
 *   status  – published (is_visible=1) | hidden (is_visible=0)
 */
class AdminAdReviewFilter extends BaseFilters
{
    protected $filters = [
        'search',
        'status',
    ];

    /**
     * Search by user name or user phone.
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
     * Filter by visibility status:
     *   published → is_visible = 1
     *   hidden    → is_visible = 0
     */
    protected function status(string $value): void
    {
        $isVisible = $value === 'published' ? 1 : 0;
        $this->builder->where('is_visible', $isVisible);
    }
}
