<?php

namespace App\Providers;

use App\Repositories\Contracts\AdminRepositoryContract;
use App\Repositories\Contracts\AdPackageRepositoryContract;
use App\Repositories\Contracts\RoleRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\AdminRepository;
use App\Repositories\AdPackageRepository;
use App\Repositories\ContactMessageRepository;
use App\Repositories\Contracts\ContactMessageRepositoryContract;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(RoleRepositoryContract::class, RoleRepository::class);
        $this->app->bind(AdminRepositoryContract::class, AdminRepository::class);
        $this->app->bind(ContactMessageRepositoryContract::class, ContactMessageRepository::class);
        $this->app->bind(AdPackageRepositoryContract::class, AdPackageRepository::class);
    }
}
