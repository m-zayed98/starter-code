<?php

namespace App\Repositories;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class TransactionRepository extends BaseRepository implements TransactionRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new Transaction();
    }

    /**
     * Find a pending transaction by its ID and user.
     * Eager-loads `transactionable` so callers don't need a second query.
     */
    public function findPendingByIdAndUser(int $transactionId, int $userId): ?Transaction
    {
        return $this->model->newQuery()
            ->where('id', $transactionId)
            ->where('user_id', $userId)
            ->where('status', TransactionStatus::PENDING->value)
            ->with(['transactionable'])
            ->first();
    }

    /**
     * Update the status, optional reference, and optional meta of a transaction
     * in a single DB call.
     */
    public function updateStatus(int $transactionId, string $status, ?array $meta = null, ?string $reference = null): Transaction
    {
        $data = ['status' => $status];

        if ($meta !== null) {
            $data['meta'] = $meta;
        }

        if ($reference !== null) {
            $data['reference'] = $reference;
        }

        return $this->update($transactionId, $data);
    }
}
