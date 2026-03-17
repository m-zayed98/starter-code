<?php

namespace App\Http\Filters;

class ContactMessageFilter extends BaseFilters
{
    /**
     * Registered filters to operate upon.
     *
     * @var array
     */
    protected $filters = [
        'search',
        'name',
        'phone',
        'email',
        'message_type',
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
            $query->where('name', 'like', "%{$value}%")
                ->orWhere('phone', 'like', "%{$value}%")
                ->orWhere('email', 'like', "%{$value}%");
        });
    }

    protected function name($value)
    {
        dd(request()->all());
        $this->builder->where('name', 'like', "%{$value}%");
    }

    protected function phone($value)
    {
        $this->builder->where('phone', 'like', "%{$value}%");
    }

    protected function email($value)
    {
        $this->builder->where('email', 'like', "%{$value}%");
    }

    protected function message_type($value)
    {
        dd($value);
        $this->builder->where('message_type', $value);
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
