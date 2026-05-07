<?php

namespace App\Providers;

use App\Repositories\Contracts\AdminRepositoryContract;
use App\Repositories\Contracts\AdPackageRepositoryContract;
use App\Repositories\Contracts\AdRepositoryContract;
use App\Repositories\Contracts\AdReportRepositoryContract;
use App\Repositories\Contracts\AdReviewRepositoryContract;
use App\Repositories\Contracts\BlogRepositoryContract;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Repositories\Contracts\FcmTokenRepositoryContract;
use App\Repositories\Contracts\NafathVerificationRequestRepositoryContract;
use App\Repositories\Contracts\NotificationGroupRepositoryContract;
use App\Repositories\Contracts\RoleRepositoryContract;
use App\Repositories\Contracts\SubscriptionRepositoryContract;
use App\Repositories\Contracts\TransactionRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\AdminRepository;
use App\Repositories\AdPackageRepository;
use App\Repositories\AdRepository;
use App\Repositories\AdReportRepository;
use App\Repositories\AdReviewRepository;
use App\Repositories\BlogRepository;
use App\Repositories\CommentRepository;
use App\Repositories\ContactMessageRepository;
use App\Repositories\Contracts\ContactMessageRepositoryContract;
use App\Repositories\FcmTokenRepository;
use App\Repositories\NafathVerificationRequestRepository;
use App\Repositories\NotificationGroupRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\TransactionRepository;
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
        $this->app->bind(AdRepositoryContract::class, AdRepository::class);
        $this->app->bind(AdReviewRepositoryContract::class, AdReviewRepository::class);
        $this->app->bind(AdReportRepositoryContract::class, AdReportRepository::class);
        $this->app->bind(NotificationGroupRepositoryContract::class, NotificationGroupRepository::class);
        $this->app->bind(BlogRepositoryContract::class, BlogRepository::class);
        $this->app->bind(CommentRepositoryContract::class, CommentRepository::class);
        $this->app->bind(SubscriptionRepositoryContract::class, SubscriptionRepository::class);
        $this->app->bind(TransactionRepositoryContract::class, TransactionRepository::class);
        $this->app->bind(FcmTokenRepositoryContract::class, FcmTokenRepository::class);
        $this->app->bind(NafathVerificationRequestRepositoryContract::class, NafathVerificationRequestRepository::class);
    }
}
