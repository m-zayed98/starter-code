<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface NotificationGroupRepositoryContract extends RepositoryContract
{
    /**
     * Sync recipients (user IDs) for a notification group.
     */
    public function syncRecipients(int $groupId, array $userIds): void;

    /**
     * Get user IDs for a notification group.
     */
    public function getRecipientIds(int $groupId): array;
}

