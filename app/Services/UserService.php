<?php

namespace App\Services;

use App\Mail\UserStatusChangedMail;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use App\Facades\MediaUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/** @property UserRepositoryContract $repository */
class UserService extends BaseModelService
{
    public function __construct(UserRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get user profile by id.
     *
     * @param int $id
     * @return Model|null
     */
    public function getProfile(int $id): ?Model
    {
        return $this->repository->show($id);
    }

    /**
     * Update the authenticated user's profile.
     * Handles avatar upload via MediaUpload; all other logic in repository.
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function updateProfile(int $id, array $data): Model
    {
        return DB::transaction(function () use ($data, $id) {
            $avatarFile = Arr::pull($data, 'avatar');

            $user = $this->repository->updateProfile($id, $data);

            if ($avatarFile instanceof UploadedFile) {
                MediaUpload::file($avatarFile)
                    ->collection('avatar')
                    ->uploadTo($user);
            }

            return $user->refresh();
        });
    }

    public function toggleStatus(User $user, ?string $reason = null): User
    {
        $newStatus = ($user->status ?? 'active') === 'inactive' ? 'active' : 'inactive';

        if ($newStatus === 'inactive' && $this->hasUpcomingOrOngoingBookings($user->id)) {
            throw new \DomainException('User has upcoming or ongoing bookings and cannot be disabled.');
        }

        DB::transaction(function () use ($user, $newStatus, $reason) {
            if ($newStatus === 'inactive') {
                $user->forceFill([
                    'status' => 'inactive',
                    'disabled_reason' => $reason ?: 'Disabled by admin',
                    'disabled_at' => now(),
                ])->save();

                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }
            } else {
                $user->forceFill([
                    'status' => 'active',
                    'disabled_reason' => null,
                    'disabled_at' => null,
                ])->save();
            }
        });

        $user = $user->refresh();

        if (!empty($user->email)) {
            Mail::to($user->email)->send(new UserStatusChangedMail(
                $user,
                $newStatus,
                $newStatus === 'inactive' ? ($user->disabled_reason ?? $reason) : null
            ));
        }

        return $user;
    }

    private function hasUpcomingOrOngoingBookings(int $userId): bool
    {
        if (!Schema::hasTable('bookings')) {
            return false;
        }

        $query = DB::table('bookings');

        if (Schema::hasColumn('bookings', 'user_id')) {
            $query->where('user_id', $userId);
        } else {
            return false;
        }

        if (Schema::hasColumn('bookings', 'status')) {
            $query->whereIn('status', ['upcoming', 'ongoing']);
        } elseif (Schema::hasColumn('bookings', 'start_at')) {
            $query->where('start_at', '>=', now());
        } elseif (Schema::hasColumn('bookings', 'start_date')) {
            $query->where('start_date', '>=', now()->toDateString());
        } else {
            return false;
        }

        return $query->exists();
    }
}
