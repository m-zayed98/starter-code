<?php

namespace App\Repositories\Contracts;

use App\Models\NafathVerificationRequest;

interface NafathVerificationRequestRepositoryContract extends RepositoryContract
{
    /**
     * Find a verification request by its trans_id.
     */
    public function findByTransId(string $transId): ?NafathVerificationRequest;

    /**
     * Find the latest pending request for a user.
     */
    public function findPendingByUser(int $userId): ?NafathVerificationRequest;

    /**
     * Update the status of a verification request.
     */
    public function updateStatus(int $id, string $status): NafathVerificationRequest;

    /**
     * Mark all pending requests for a user as expired.
     */
    public function expirePendingForUser(int $userId): void;
}
