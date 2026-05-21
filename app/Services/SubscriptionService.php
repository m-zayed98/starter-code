<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\SubscriptionException;
use App\Models\AdPackage;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Repositories\Contracts\AdPackageRepositoryContract;
use App\Repositories\Contracts\SubscriptionRepositoryContract;
use App\Repositories\Contracts\TransactionRepositoryContract;
use Illuminate\Support\Str;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepositoryContract $subscriptionRepository,
        private readonly TransactionRepositoryContract $transactionRepository,
        private readonly AdPackageRepositoryContract $adPackageRepository,
    ) {}

    // ─── Public API ───────────────────────────────────────────────────────

    /**
     * Initiate a subscription request for a user.
     *
     * Validates business rules, creates a pending Transaction, and returns it.
     * The subscription itself is only created once the transaction is completed.
     *
     * @throws SubscriptionException
     */
    public function initiateSubscription(int $userId, int $packageId): Transaction
    {
        /** @var AdPackage $package */
        $package = $this->adPackageRepository->showOrFail($packageId);

        $this->assertPackageAvailable($package);
        $this->assertUserHasNoActiveSubscription($userId);

        $transaction = $this->transactionRepository->create([
            'user_id' => $userId,
            'transactionable_type' => AdPackage::class,
            'transactionable_id' => $package->id,
            'amount' => $package->price,
            'status' => TransactionStatus::COMPLETED->value,
            'reference' => Str::uuid()->toString(),
        ])->load(['transactionable']);

        $startsAt = now()->toDateString();
        $expiresAt = now()->addDays($package->duration_days)
            ->addHours($package->duration_hours)
            ->toDateString();

        $this->subscriptionRepository->createFromTransaction([
            'user_id' => $transaction->user_id,
            'ad_package_id' => $package->id,
            'ad_count' => $package->ads_count,
            'user_ads_count' => 0,
            'package_price' => $package->price,
            'status' => SubscriptionStatus::ACTIVE->value,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_cancelled' => false,
        ]);

        return $transaction;
    }

    /**
     * Return the active subscription for a user, or null.
     * Eager-loads adPackage — use for SubscriptionResource rendering.
     */
    public function getActiveSubscription(int $userId): ?Subscription
    {
        return $this->subscriptionRepository->findActiveByUser($userId);
    }

    /**
     * Return only the active subscription's package ID for a user.
     * Cheaper than getActiveSubscription — use when only the ID is needed
     * (e.g. stamping is_subscribed on package listings).
     */
    public function getActivePackageId(int $userId): ?int
    {
        return $this->subscriptionRepository->findActivePackageIdByUser($userId);
    }

    /**
     * Create a Subscription after a transaction has been completed.
     *
     * Expects `transaction->transactionable` to already be loaded (done by
     * TransactionRepository::findPendingByIdAndUser and re-attached by
     * TransactionService after the status update) — no extra DB query needed.
     *
     * Called internally by TransactionService when a payment succeeds.
     */
    public function createFromCompletedTransaction(Transaction $transaction): Subscription
    {
        /** @var AdPackage $package */
        $package = $transaction->transactionable;

        // Fallback: if relation wasn't loaded for any reason, fetch it once
        if (! $package instanceof AdPackage) {
            $package = $this->adPackageRepository->showOrFail($transaction->transactionable_id);
        }

        $startsAt = now()->toDateString();
        $expiresAt = now()->addDays($package->duration_days)->toDateString();

        return $this->subscriptionRepository->createFromTransaction([
            'user_id' => $transaction->user_id,
            'ad_package_id' => $package->id,
            'ad_count' => $package->ads_count,
            'user_ads_count' => 0,
            'package_price' => $package->price,
            'status' => SubscriptionStatus::ACTIVE->value,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_cancelled' => false,
        ]);
    }

    // ─── Guards ───────────────────────────────────────────────────────────

    /**
     * @throws SubscriptionException
     */
    private function assertPackageAvailable(AdPackage $package): void
    {
        if (! $package->is_active) {
            throw SubscriptionException::packageNotAvailable();
        }

        // For offer packages: check date range and subscriber cap
        if ($package->type->value === 'offer') {
            $today = now()->toDateString();

            // Offer hasn't started yet
            if ($package->start_date && $package->start_date->toDateString() > $today) {
                throw SubscriptionException::offerNotStartedYet();
            }

            // Offer has expired
            if ($package->end_date && $package->end_date->toDateString() < $today) {
                throw SubscriptionException::packageNotAvailable();
            }

            // Subscriber cap reached
            if ($package->max_subscribers !== null) {
                $activeCount = $this->adPackageRepository->countActiveSubscriptions($package->id);
                if ($activeCount >= $package->max_subscribers) {
                    throw SubscriptionException::packageNotAvailable();
                }
            }
        }
    }

    /**
     * @throws SubscriptionException
     */
    private function assertUserHasNoActiveSubscription(int $userId): void
    {
        if ($this->subscriptionRepository->userHasActiveSubscription($userId)) {
            throw SubscriptionException::alreadySubscribed();
        }
    }
}
