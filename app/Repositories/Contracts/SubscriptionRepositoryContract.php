<?php

namespace App\Repositories\Contracts;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Model;

interface SubscriptionRepositoryContract extends RepositoryContract
{
    /**
     * Find the active subscription for a given user (not cancelled, not expired).
     * Eager-loads `adPackage` for use in SubscriptionResource.
     */
    public function findActiveByUser(int $userId): ?Subscription;

    /**
     * Return only the active subscription's package ID for a user.
     * Cheaper than findActiveByUser — no model hydration of relations.
     */
    public function findActivePackageIdByUser(int $userId): ?int;

    /**
     * Check whether a user has any active subscription.
     */
    public function userHasActiveSubscription(int $userId): bool;

    /**
     * Create a subscription from a completed transaction.
     */
    public function createFromTransaction(array $data): Subscription;
}
