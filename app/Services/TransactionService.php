<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryContract;
use App\Services\Transaction\TransactionHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles the lifecycle of a Transaction after a gateway callback arrives.
 *
 * Flow:
 *   1. Client calls POST /transactions/{id}/process with { status, reference?, ... }
 *   2. TransactionService::process() is called
 *   3. TransactionHelper resolves the terminal status from the validated payload
 *   4. COMPLETED  → update transaction + create Subscription via SubscriptionService
 *   5. FAILED     → update transaction + log (notification hook ready)
 *   6. CANCELLED  → update transaction + log
 */
class TransactionService
{
    public function __construct(
        private readonly TransactionRepositoryContract $transactionRepository,
        private readonly SubscriptionService           $subscriptionService,
        private readonly TransactionHelper             $transactionHelper,
    ) {}

    /**
     * Process a gateway callback for a pending transaction.
     *
     * `findPendingByIdAndUser` already eager-loads `transactionable`, so
     * downstream calls (e.g. createFromCompletedTransaction) reuse it
     * without an extra query.
     *
     * @param  array<string, mixed>  $payload  Validated request data
     * @throws ModelNotFoundException  When no pending transaction matches
     */
    public function process(int $transactionId, int $userId, array $payload): Transaction
    {
        // Fetches only pending transactions; also eager-loads transactionable
        $transaction = $this->transactionRepository->findPendingByIdAndUser($transactionId, $userId);

        if ($transaction === null) {
            throw new ModelNotFoundException(
                "Pending transaction [{$transactionId}] not found for this user."
            );
        }

        $status    = $this->transactionHelper->resolveStatus($payload);
        $reference = $this->transactionHelper->extractReference($payload);
        $meta      = $this->transactionHelper->buildMeta($payload);

        return DB::transaction(function () use ($transaction, $status, $reference, $meta) {
            // Single DB call: status + reference + meta together
            $updated = $this->transactionRepository->updateStatus(
                $transaction->id,
                $status->value,
                $meta,
                $reference,
            );

            // Re-attach the already-loaded relation so downstream services
            // don't fire another query for the transactionable model.
            $updated->setRelation('transactionable', $transaction->transactionable);

            match ($status) {
                TransactionStatus::COMPLETED => $this->handleCompleted($updated),
                TransactionStatus::FAILED    => $this->handleFailed($updated),
                TransactionStatus::CANCELLED => $this->handleCancelled($updated),
            };

            return $updated;
        });
    }

    // ─── Status handlers ──────────────────────────────────────────────────

    private function handleCompleted(Transaction $transaction): void
    {
        $this->subscriptionService->createFromCompletedTransaction($transaction);
    }

    private function handleFailed(Transaction $transaction): void
    {
        // TODO: dispatch a notification to the user about the failed payment
        Log::info("Transaction [{$transaction->id}] failed for user [{$transaction->user_id}].");
    }

    private function handleCancelled(Transaction $transaction): void
    {
        Log::info("Transaction [{$transaction->id}] cancelled by user [{$transaction->user_id}].");
    }
}
