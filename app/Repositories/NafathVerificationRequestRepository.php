<?php

namespace App\Repositories;

use App\Enums\NafathVerificationStatus;
use App\Models\NafathVerificationRequest;
use App\Repositories\Contracts\NafathVerificationRequestRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class NafathVerificationRequestRepository extends BaseRepository implements NafathVerificationRequestRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new NafathVerificationRequest();
    }

    /**
     * Find a verification request by its trans_id.
     */
    public function findByTransId(string $transId): ?NafathVerificationRequest
    {
        /** @var NafathVerificationRequest|null */
        return $this->findBy('trans_id', $transId);
    }

    /**
     * Find the latest pending request for a user.
     */
    public function findPendingByUser(int $userId): ?NafathVerificationRequest
    {
        /** @var NafathVerificationRequest|null */
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('status', NafathVerificationStatus::PENDING->value)
            ->latest()
            ->first();
    }

    /**
     * Update the status of a verification request.
     */
    public function updateStatus(int $id, string $status): NafathVerificationRequest
    {
        /** @var NafathVerificationRequest */
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Mark all pending requests for a user as expired.
     */
    public function expirePendingForUser(int $userId): void
    {
        $this->newQuery()
            ->where('user_id', $userId)
            ->where('status', NafathVerificationStatus::PENDING->value)
            ->update(['status' => NafathVerificationStatus::EXPIRED->value]);
    }
}
