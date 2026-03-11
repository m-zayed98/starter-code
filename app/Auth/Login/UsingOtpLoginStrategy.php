<?php

namespace App\Auth\Login;

use App\Models\Otp;
use App\Traits\HasOtps;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class UsingOtpLoginStrategy implements LoginStrategy
{
    public function __construct(
        private readonly string $purpose = 'login',
        private readonly int $ttlMinutes = 10,
        private readonly int $length = 6
    ) {
    }

    public function login(string $guard, string $loginKey, string $authModel, array $credentials): array
    {
        $loginValue = $credentials[$loginKey] ?? null;

        if (!is_string($loginValue) || $loginValue === '') {
            throw ValidationException::withMessages([
                $loginKey => ['Invalid credentials.'],
            ]);
        }

        /** @var \Illuminate\Database\Eloquent\Model&Authenticatable|null $user */
        $user = $authModel::query()->where($loginKey, $loginValue)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                $loginKey => ['Invalid credentials.'],
            ]);
        }

        $expiresAt = now()->addMinutes($this->ttlMinutes);

        if (in_array(HasOtps::class, class_uses_recursive($user), true) && method_exists($user, 'createOtp')) {
            $otp = $user->createOtp($this->purpose, $expiresAt, $this->length);
        } else {
            /** @var \Illuminate\Database\Eloquent\Model $user */
            $otp = Otp::query()->create([
                'otpable_type' => $user::class,
                'otpable_id' => $user->getKey(),
                'code' => (string) random_int(10 ** ($this->length - 1), (10 ** $this->length) - 1),
                'purpose' => $this->purpose,
                'is_used' => false,
                'expires_at' => $expiresAt,
                'used_at' => null,
            ]);
        }

        return [
            'user' => $user,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'purpose' => $this->purpose,
        ];
    }
}

