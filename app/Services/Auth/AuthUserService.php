<?php

namespace App\Services\Auth;

use App\Auth\Login\UsingOtpLoginStrategy;
use App\Enums\OtpPurpose;
use App\Facades\MediaUpload;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\Auth\Contracts\AuthLoginServiceContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AuthUserService implements AuthLoginServiceContract
{
    protected string $guard = 'api';

    protected string $authModel = User::class;

    private const OTP_LENGTH = 6;

    /**
     * Register a new user, upload avatar, and send OTP.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function register(array $data): array
    {
        $user = DB::transaction(function () use ($data) {
            $avatarFile = Arr::pull($data, 'avatar');
            $user = User::query()->create($data);

            if ($avatarFile instanceof UploadedFile) {
                $user->clearMediaCollection('avatar');
                MediaUpload::file($avatarFile)
                    ->collection('avatar')
                    ->uploadTo($user);
            }

            $expiresAt = now()->addMinutes(10);
            $user->createOtp(OtpPurpose::REGISTER->value, $expiresAt, self::OTP_LENGTH);

            return $user->refresh();
        });

        $otp = $user->latestValidOtp(OtpPurpose::REGISTER->value);

        return [
            'message' => 'Registration successful. Please verify OTP.',
            'user' => UserResource::make($user),
            'expires_at' => $otp?->expires_at?->toIso8601String(),
            'purpose' => OtpPurpose::REGISTER->value,
            'otp' => $otp?->code,
        ];
    }

    /**
     * Attempt login (sends OTP). Returns response data for API.
     *
     * @param array<string, mixed> $credentials
     * @return array<string, mixed>
     */
    public function login(array $credentials, string $authKey): array
    {
        $strategy = app()->makeWith(UsingOtpLoginStrategy::class, [
            'purpose' => OtpPurpose::LOGIN->value,
            'length' => self::OTP_LENGTH,
        ]);

        $result = $strategy->login(
            $this->guard,
            $authKey,
            $this->authModel,
            $credentials
        );

        return [
            'message' => 'OTP sent successfully.',
            'user' => UserResource::make($result['user']),
            'expires_at' => $result['expires_at']->toIso8601String(),
            'purpose' => $result['purpose'],
            'otp' => $result['otp']->code,
        ];
    }

    /**
     * Resend OTP: expire old OTPs for the purpose, create a new one, return the new code.
     *
     * @param array<string, mixed> $data Must contain purpose and either email or phone (from getLoginKeyAndValue)
     * @return array<string, mixed>
     */
    public function resendOtp(array $data, string $loginKey, string $loginValue, ?string $countryCode = null): array
    {
        $purpose = $data['purpose'];

        $query = User::query()->where($loginKey, $loginValue);
        if ($loginKey === 'phone' && is_string($countryCode) && $countryCode !== '') {
            $query->where('country_code', $countryCode);
        }

        $user = $query->first();

        if (! $user || ! method_exists($user, 'otps')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $loginKey => ['User not found.'],
            ]);
        }

        $user->otps()
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->get()
            ->each(fn($otp) => $otp->markUsed());

        $expiresAt = now()->addMinutes(10);
        $otp = $user->createOtp($purpose, $expiresAt, self::OTP_LENGTH);

        return [
            'message' => 'New OTP sent successfully.',
            'expires_at' => $otp->expires_at->toIso8601String(),
            'purpose' => $purpose,
            'otp' => $otp->code,
        ];
    }
}
