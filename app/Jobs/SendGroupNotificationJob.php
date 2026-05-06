<?php

namespace App\Jobs;

use App\Models\NotificationGroup;
use App\Notifications\AdminNotification;
use App\Models\User;
use App\Repositories\Contracts\NotificationGroupRepositoryContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGroupNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 0;

    public function __construct(
        public readonly int $notificationGroupId
    ) {}

    public function handle(NotificationGroupRepositoryContract $repository): void
    {
        /** @var NotificationGroup $group */
        $group = $repository->showOrFail($this->notificationGroupId);

        if ($group->status === NotificationGroup::STATUS_SENT) {
            return;
        }

        $repository->update($group->id, ['status' => NotificationGroup::STATUS_SENDING]);

        try {
            $userIds = $repository->getRecipientIds($group->id);

            User::query()
                ->whereIn('id', $userIds)
                ->orderBy('id')
                ->chunkById(10, function ($users) use ($group) {
                    foreach ($users as $user) {
                        $user->notify(new AdminNotification($group));
                    }
                });

            $repository->update($group->id, [
                'status'  => NotificationGroup::STATUS_SENT,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $repository->update($group->id, ['status' => NotificationGroup::STATUS_FAILED]);

            throw $e;
        }
    }
}

