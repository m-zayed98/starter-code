<?php

namespace App\Repositories;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new Subscription();
    }

    /**
     * Find the active subscription for a given user.
     * Active = status 'active', not cancelled, not expired.
     *
     * Eager-loads `adPackage` so the SubscriptionResource can render
     * package details without an extra query.
     */
    public function findActiveByUser(int $userId): ?Subscription
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('is_cancelled', false)
            ->where('expires_at', '>=', now()->toDateString())
            ->with(['adPackage'])
            ->first();
    }

    /**
     * Return only the active subscription's package ID for a user.
     * Uses a scalar query — no model hydration, no relation loading.
     */
    public function findActivePackageIdByUser(int $userId): ?int
    {
        $result = $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('is_cancelled', false)
            ->where('expires_at', '>=', now()->toDateString())
            ->value('ad_package_id');

        return $result !== null ? (int) $result : null;
    }

    /**
     * Check whether a user has any active subscription.
     * Uses EXISTS — no model hydration, no relation loading.
     */
    public function userHasActiveSubscription(int $userId): bool
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('is_cancelled', false)
            ->where('expires_at', '>=', now()->toDateString())
            ->exists();
    }

    /**
     * Persist a new subscription from a completed-transaction payload.
     * Uses the base repository create() to stay consistent with the pattern.
     */
    public function createFromTransaction(array $data): Subscription
    {
        /** @var Subscription $subscription */
        $subscription = $this->create($data);

        return $subscription->load(['adPackage']);
    }
}
