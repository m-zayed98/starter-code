<?php

namespace App\Http\Filters;

class UserFilter extends BaseFilters
{
    protected $filters = [
        'search',
        'status',
        'email',
        'role',
    ];

    protected function search($value)
    {
        $this->builder->where(function ($query) use ($value) {
            $query->where('name', 'like', "%{$value}%")
                  ->orWhere('email', 'like', "%{$value}%");
        });
    }

    protected function status($value)
    {
        $this->builder->where('status', $value);
    }

    protected function email($value)
    {
        $this->builder->where('email', 'like', "%{$value}%");
    }

    protected function role($value)
    {
        $this->builder->whereHas('roles', function ($query) use ($value) {
            $query->where('name', $value);
        });
    }
}
