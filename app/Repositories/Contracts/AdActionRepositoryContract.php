<?php

namespace App\Repositories\Contracts;

use App\Enums\AdActionType;

interface AdActionRepositoryContract extends RepositoryContract
{
    /**
     * Record an action for an ad by a user (deduplicated per user per ad per type).
     * For authenticated users, uses updateOrCreate to prevent duplicates.
     * For guests (userId = null), always inserts a new row.
     */
    public function recordAction(int $adId, ?int $userId, AdActionType $type): void;

    /**
     * Check whether a user has already performed a specific action on an ad.
     */
    public function hasUserPerformedAction(int $adId, int $userId, AdActionType $type): bool;

    /**
     * Count total actions of a given type across all ads.
     */
    public function countByType(AdActionType $type): int;
}
