<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\OtpPurpose;
use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseAuthController;
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

        return ApiResponse::respondWithArray($data)->send();
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $formRequest = app(UserVerifyOtpRequest::class);
        $data = $formRequest->validated();
        [$loginKey, $loginValue, $countryCode] = $formRequest->getLoginKeyAndValue();

        try {
            $purpose = OtpPurpose::from($data['purpose']);
        } catch (\ValueError) {
            return ApiResponse::respondWithError(__('Invalid OTP purpose.'), httpStatus: 422)->send();
        }

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

        $valid = $user->consumeOtp($purpose->value, $data['code']);

        if (! $valid) {
            return ApiResponse::respondWithError(__('Invalid or expired OTP.'), httpStatus: 422)->send();
        }

        $token = $user->createToken($this->guard)->plainTextToken;

        return ApiResponse::respondWithArray([
            'verified' => true,
            'user' => UserResource::make($user),
            'token' => $token,
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
