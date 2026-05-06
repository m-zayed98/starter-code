<?php

namespace App\Http\Filters;

class NotificationGroupFilter extends BaseFilters
{
    protected $filters = [
        'search',
        'status',
    ];

    protected function search(string $value): void
    {
        $this->builder->where(function ($query) use ($value) {
            $query->where('title->en', 'like', "%{$value}%")
                ->orWhere('title->ar', 'like', "%{$value}%")
                ->orWhere('body->en', 'like', "%{$value}%")
                ->orWhere('body->ar', 'like', "%{$value}%");
        });
    }

    protected function status(string $value): void
    {
        $this->builder->where('status', $value);
    }
}
