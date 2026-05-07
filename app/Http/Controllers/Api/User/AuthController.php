<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\OtpPurpose;
use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseAuthController;
use App\Http\Requests\User\ChangePhoneRequest;
use App\Http\Requests\User\LogoutRequest;
use App\Http\Requests\User\UserRegisterRequest;
use App\Http\Requests\User\UserResendOtpRequest;
use App\Http\Requests\User\UserVerifyOtpRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\Auth\AuthUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseAuthController
{
    protected string $guard = 'api';

    protected string $authModel = User::class;

    protected string $loginKey = 'email';

    protected $loginFormRequest = \App\Http\Requests\User\UserLoginRequest::class;

    protected $authService = AuthUserService::class;

    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $service = app(AuthUserService::class);
        $data = $service->register($data);

        return ApiResponse::respondWithArray($data , __('Registration successful. Please verify OTP.'))->send();
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $formRequest = app(UserVerifyOtpRequest::class);
        $data = $formRequest->validated();

        try {
            $purpose = OtpPurpose::from($data['purpose']);
        } catch (\ValueError) {
            return ApiResponse::respondWithError(__('Invalid OTP purpose.'), httpStatus: 422)->send();
        }

        // For change_phone the user is already authenticated; no need to look them up by credentials
        if ($purpose === OtpPurpose::CHANGE_PHONE) {
            /** @var \App\Models\User|null $user */
            $user = auth($this->guard)->user();

            if (! $user) {
                return ApiResponse::respondWithError(__('Unauthenticated.'), httpStatus: 401)->send();
            }
        } else {
            [$loginKey, $loginValue, $countryCode] = $formRequest->getLoginKeyAndValue();

            $query = User::query()->where($loginKey, $loginValue);
            if ($loginKey === 'phone' && is_string($countryCode) && $countryCode !== '') {
                $query->where('country_code', $countryCode);
            }

            $user = $query->first();

            if (! $user || ! method_exists($user, 'consumeOtp')) {
                return ApiResponse::respondWithError(__('Invalid user.'), httpStatus: 404)->send();
            }

            if (($user->status ?? null) === 'inactive') {
                return ApiResponse::respondWithError(__('Your account has been disabled. Please contact the administration.'), httpStatus: 403)->send();
            }
        }

        $otp = $user->consumeOtp($purpose->value, $data['code']);

        if (! $otp) {
            return ApiResponse::respondWithError(__('Invalid or expired OTP.'), httpStatus: 422)->send();
        }

        // Handle purpose-specific post-verification logic
        if ($purpose === OtpPurpose::CHANGE_PHONE) {
            $additionalData = $otp->additional_data ?? [];
            $user->phone        = $additionalData['phone'] ?? $user->phone;
            $user->country_code = $additionalData['country_code'] ?? $user->country_code;
            $user->save();
            $user->refresh();
        }

        $token = $user->createToken($this->guard)->plainTextToken;

        return ApiResponse::respondWithArray([
            'verified' => true,
            'user'     => UserResource::make($user),
            'token'    => $token,
        ])->send();
    }

    public function changePhone(ChangePhoneRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth($this->guard)->user();

        if (! $user) {
            return ApiResponse::respondWithError(__('Unauthenticated.'), httpStatus: 401)->send();
        }

        $data = $request->validated();

        $expiresAt = now()->addMinutes(10);
        $otp = $user->createOtp(
            OtpPurpose::CHANGE_PHONE->value,
            $expiresAt,
            6,
            true,
            [
                'phone'        => $data['phone'],
                'country_code' => $data['country_code'],
            ]
        );

        return ApiResponse::respondWithArray([
            'message'    => __('OTP sent successfully.'),
            'expires_at' => $otp->expires_at->toIso8601String(),
            'purpose'    => OtpPurpose::CHANGE_PHONE->value,
            'otp'        => $otp->code,
        ])->send();
    }

    public function resendOtp(UserResendOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        [$loginKey, $loginValue, $countryCode] = $request->getLoginKeyAndValue();
        $service = app(AuthUserService::class);
        $result = $service->resendOtp($data, $loginKey, $loginValue, $countryCode);

        return ApiResponse::respondWithArray($result)->send();
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        $user = auth($this->guard)->user();
        if (! $user) {
            return ApiResponse::respondWithError(__('Unauthenticated.'), httpStatus: 401)->send();
        }

        $data = $request->validated();

        $token = $user->currentAccessToken();
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token?->delete();

        if (! empty($data['device_token'])) {
            // TODO: remove device token association (persistence not implemented yet).
        }

        return ApiResponse::respondWithSuccess(message: __('Logged out successfully'))->send();
    }
}
