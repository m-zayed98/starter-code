<?php

namespace App\Services;

use App\Jobs\SendGroupNotificationJob;
use App\Models\NotificationGroup;
use App\Repositories\Contracts\NotificationGroupRepositoryContract;
use App\Repositories\DTOs\QueryOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/** @property NotificationGroupRepositoryContract $repository */
class NotificationGroupService extends BaseModelService
{
    public function __construct(NotificationGroupRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function get(array|QueryOptions $options = []): Collection|LengthAwarePaginator
    {
        $options = $this->normalizeOptions($options);

        // Always load creator and count recipients
        $relations = array_merge($options->relations, ['creator']);
        $options = QueryOptions::make(array_merge($options->toArray(), [
            'relations' => $relations,
        ]));

        $result = $this->repository->get($options);

        // Load counts
        if ($result instanceof LengthAwarePaginator) {
            $result->getCollection()->loadCount('recipients');
        } else {
            $result->loadCount('recipients');
        }

        return $result;
    }

    public function showOrFail(int $id, array|QueryOptions $options = []): Model
    {
        $options = $this->normalizeOptions($options);

        // Always load creator and recipients for detail view
        $relations = array_merge($options->relations, ['creator', 'recipients']);
        $options = QueryOptions::make(array_merge($options->toArray(), [
            'relations' => $relations,
        ]));

        $model = $this->repository->showOrFail($id, $options);
        $model->loadCount('recipients');

        return $model;
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            /** @var int[] $userIds */
            $userIds = Arr::pull($data, 'user_ids', []);

            $group = $this->repository->create([
                'title' => $data['title'],
                'body' => $data['body'],
                'status' => NotificationGroup::STATUS_PENDING,
                'created_by' => $data['created_by'],
            ]);

            $this->repository->syncRecipients($group->id, $userIds);

            return $this->repository->showOrFail($group->id, ['relations' => ['creator']]);
        });
    }

    public function delete(int $id, bool $forceDelete = false): bool
    {
        /** @var NotificationGroup $group */
        $group = $this->repository->showOrFail($id);

        if ($group->status !== NotificationGroup::STATUS_PENDING) {
            throw new \DomainException(__('Only pending notifications can be deleted.'));
        }

        return $this->repository->delete($id, $forceDelete);
    }

    public function send(int $groupId): void
    {
        SendGroupNotificationJob::dispatch($groupId);
    }

    public function createAndSend(array $data): Model
    {
        $group = $this->create($data);
        $this->send($group->id);

        // Reload with counts
        $group->loadCount('recipients');

        return $group;
    }

    private function normalizeOptions(array|QueryOptions $options): QueryOptions
    {
        return $options instanceof QueryOptions
            ? $options
            : QueryOptions::make($options);
    }
}
