<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseFilters
{
    /**
     * The Eloquent builder instance.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Registered filters to operate upon.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Create a new BaseFilters instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply the filters.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $filter => $value) {
            $methodName = $this->filterMethodName($filter);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }

        return $this->builder;
    }

    /**
     * Get all the filters that can be applied.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->request->only($this->filters);
    }

    /**
     * Convert filter name to method name (snake_case to camelCase).
     *
     * @param string $filter
     * @return string
     */
    protected function filterMethodName($filter)
    {
        return \Illuminate\Support\Str::camel($filter);
    }
}
