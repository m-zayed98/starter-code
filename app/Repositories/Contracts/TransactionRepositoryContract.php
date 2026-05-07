<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;

interface TransactionRepositoryContract extends RepositoryContract
{
    /**
     * Find a pending transaction by its ID and user.
     * Eager-loads `transactionable` so callers avoid a second query.
     */
    public function findPendingByIdAndUser(int $transactionId, int $userId): ?Transaction;

    /**
     * Update the status, optional reference, and optional meta of a transaction
     * in a single DB call.
     */
    public function updateStatus(int $transactionId, string $status, ?array $meta = null, ?string $reference = null): Transaction;
}
