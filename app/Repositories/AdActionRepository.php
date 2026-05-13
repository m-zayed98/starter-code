<?php

namespace App\Repositories;

use App\Enums\AdActionType;
use App\Models\AdAction;
use App\Repositories\Contracts\AdActionRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class AdActionRepository extends BaseRepository implements AdActionRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new AdAction();
    }

    /**
     * Record an action for an ad.
     *
     * - Authenticated users: deduplicated via updateOrCreate (one row per user/ad/type).
     * - Guests (userId = null): always insert a new row (no deduplication possible).
     */
    public function recordAction(int $adId, ?int $userId, AdActionType $type): void
    {
        if ($userId !== null) {
            $this->model->updateOrCreate(
                ['ad_id' => $adId, 'user_id' => $userId, 'type' => $type->value],
                ['ad_id' => $adId, 'user_id' => $userId, 'type' => $type->value],
            );
        } else {
            $this->model->create([
                'ad_id'   => $adId,
                'user_id' => null,
                'type'    => $type->value,
            ]);
        }
    }

    /**
     * Check whether an authenticated user has already performed a specific action on an ad.
     */
    public function hasUserPerformedAction(int $adId, int $userId, AdActionType $type): bool
    {
        return $this->newQuery()
            ->where('ad_id', $adId)
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->exists();
    }

    /**
     * Count total actions of a given type across all ads.
     */
    public function countByType(AdActionType $type): int
    {
        return $this->newQuery()
            ->where('type', $type->value)
            ->count();
    }
}
