<?php

namespace App\Repositories;

use App\Http\Filters\NotificationGroupFilter;
use App\Models\NotificationGroup;
use App\Repositories\Contracts\NotificationGroupRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotificationGroupRepository extends BaseRepository implements NotificationGroupRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new NotificationGroup();
    }

    protected function resolveFilter(): ?NotificationGroupFilter
    {
        return new NotificationGroupFilter(request());
    }

    public function syncRecipients(int $groupId, array $userIds): void
    {
        $uniqueUserIds = array_values(array_unique($userIds));
        
        /** @var NotificationGroup $group */
        $group = $this->showOrFail($groupId);
        $group->recipients()->sync($uniqueUserIds);
    }

    public function getRecipientIds(int $groupId): array
    {
        return DB::table('notification_group_user')
            ->where('notification_group_id', $groupId)
            ->orderBy('user_id')
            ->pluck('user_id')
            ->all();
    }
}

