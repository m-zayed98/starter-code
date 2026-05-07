<?php

namespace App\Repositories\Contracts;

use App\Models\FcmToken;
use Illuminate\Database\Eloquent\Collection;

interface FcmTokenRepositoryContract
{
    /**
     * Store a new FCM token for a user, ignoring duplicates.
     */
    public function storeForUser(int $userId, string $token, ?string $deviceType = null): FcmToken;

    /**
     * Get all FCM tokens for a given user.
     *
     * @return Collection<int, FcmToken>
     */
    public function getByUser(int $userId): Collection;

    /**
     * Delete a specific FCM token.
     */
    public function deleteToken(string $token): bool;

    /**
     * Delete all FCM tokens for a user.
     */
    public function deleteAllForUser(int $userId): int;
}
