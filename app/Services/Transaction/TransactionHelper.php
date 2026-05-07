<?php

namespace App\Services\Transaction;

use App\Enums\TransactionStatus;
use App\Models\Transaction;

/**
 * Stateless helper that processes a raw gateway callback payload
 * and maps it to a TransactionStatus.
 *
 * In a real integration this class would verify signatures, parse
 * gateway-specific response codes, etc.  For now it provides a
 * simple mock implementation.
 */
class TransactionHelper
{
    /**
     * Resolve the TransactionStatus from a gateway callback payload.
     *
     * @param  array<string, mixed>  $payload  Raw data from the payment gateway
     */
    public function resolveStatus(array $payload): TransactionStatus
    {
        $raw = strtolower($payload['status'] ?? '');

        return match ($raw) {
            'completed', 'success', 'paid' => TransactionStatus::COMPLETED,
            'cancelled', 'canceled'        => TransactionStatus::CANCELLED,
            'failed', 'error', 'declined'  => TransactionStatus::FAILED,
            default                        => TransactionStatus::PENDING,
        };
    }

    /**
     * Extract an optional external reference from the payload.
     */
    public function extractReference(array $payload): ?string
    {
        return $payload['reference'] ?? $payload['transaction_id'] ?? null;
    }

    /**
     * Build a sanitised meta array to persist alongside the transaction.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function buildMeta(array $payload): array
    {
        // Strip any sensitive keys before persisting
        $sensitive = ['card_number', 'cvv', 'password', 'secret'];

        return array_diff_key($payload, array_flip($sensitive));
    }

    /**
     * Determine whether a transaction can still be processed
     * (i.e. it is still in pending state).
     */
    public function canProcess(Transaction $transaction): bool
    {
        return $transaction->isPending();
    }
}
