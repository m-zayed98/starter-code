<?php

namespace App\Traits;

use App\Models\Otp;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait HasOtps
{
    public function otps(): MorphMany
    {
        return $this->morphMany(Otp::class, 'otpable');
    }

    public function createOtp(
        string $purpose,
        ?Carbon $expiresAt = null,
        int $length = 6,
        bool $numericOnly = true
    ): Otp {
        $expiresAt ??= now()->addMinutes(10);

        $code = $numericOnly
            ? (string) random_int(10 ** ($length - 1), (10 ** $length) - 1)
            : Str::upper(Str::random($length));

        return $this->otps()->create([
            'code' => $code,
            'purpose' => $purpose,
            'is_used' => false,
            'expires_at' => $expiresAt,
            'used_at' => null,
        ]);
    }

    public function latestValidOtp(string $purpose): ?Otp
    {
        return $this->otps()
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    public function consumeOtp(string $purpose, string $code): bool
    {
        /** @var Otp|null $otp */
        $otp = $this->otps()
            ->where('purpose', $purpose)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->markUsed();

        return true;
    }
}

