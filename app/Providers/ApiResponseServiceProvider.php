<?php

namespace App\Providers;

use App\Services\ApiResponse\ApiResponseService;
use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ApiResponseService::class, fn () => new ApiResponseService());
    }

    public function boot(): void {}
}
