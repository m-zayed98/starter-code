<?php

namespace App\Repositories;

use App\Models\FcmToken;
use App\Repositories\Contracts\FcmTokenRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

class FcmTokenRepository implements FcmTokenRepositoryContract
{
    public function storeForUser(int $userId, string $token, ?string $deviceType = null): FcmToken
    {
        /** @var FcmToken $fcmToken */
        $fcmToken = FcmToken::query()->updateOrCreate(
            ['token' => $token],
            ['user_id' => $userId, 'device_type' => $deviceType]
        );

        return $fcmToken;
    }

    public function getByUser(int $userId): Collection
    {
        return FcmToken::query()
            ->where('user_id', $userId)
            ->get();
    }

    public function deleteToken(string $token): bool
    {
        return (bool) FcmToken::query()
            ->where('token', $token)
            ->delete();
    }

    public function deleteAllForUser(int $userId): int
    {
        return FcmToken::query()
            ->where('user_id', $userId)
            ->delete();
    }
}
