<?php

namespace App\Http\Controllers\Api;

use App\Auth\Login\UsingOtpLoginStrategy;
use App\Enums\OtpPurpose;
use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;

abstract class BaseAuthController extends Controller
{
    protected string $guard = 'api';
    protected string $authModel;
    protected string $loginKey = 'email';
    protected $loginFormRequest = null;
    protected $authService = null;

    public function login(Request $request): JsonResponse
    {
        $loginFormRequest = app($this->loginFormRequest);
        $data = $loginFormRequest->validated();
        $authKey = $loginFormRequest->getAuthKey();
        $service = is_string($this->authService) ? app($this->authService) : $this->authService;
        $data = $service->login($data, $authKey);

        return ApiResponse::respondWithArray($data)->send();
    }
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            $this->loginKey => ['required', 'string'],
        ]);

        $strategy = app()->makeWith(UsingOtpLoginStrategy::class, [
            'purpose' => OtpPurpose::FORGOT_PASSWORD->value,
        ]);

        $result = $strategy->login(
            $this->guard,
            $this->loginKey,
            $this->authModel,
            $data
        );

        return ApiResponse::respondWithArray([
            'message' => 'OTP generated successfully.',
            'expires_at' => $result['expires_at'],
            'purpose' => $result['purpose'],
            'otp' => $result['otp']->code,
        ])->send();
    }
    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            $this->loginKey => ['required', 'string'],
            'code' => ['required', 'string'],
            'purpose' => ['required', 'string'],
        ]);

        try {
            $purpose = OtpPurpose::from($data['purpose']);
        } catch (\ValueError) {
            return ApiResponse::respondWithError('Invalid OTP purpose.', httpStatus: 422)->send();
        }

        $user = ($this->authModel)::query()
            ->where($this->loginKey, $data[$this->loginKey])
            ->first();

        if (! $user || ! method_exists($user, 'consumeOtp')) {
            return ApiResponse::respondWithError('Invalid user.', httpStatus: 404)->send();
        }

        if (property_exists($user, 'status') && ($user->status ?? null) === 'inactive') {
            return ApiResponse::respondWithError('لقد تم تعطيل حسابك، الرجاء التواصل مع الإدارة', httpStatus: 403)->send();
        }

        $valid = $user->consumeOtp($purpose->value, $data['code']);

        if (! $valid) {
            return ApiResponse::respondWithError('Invalid or expired OTP.', httpStatus: 422)->send();
        }
        $token = method_exists($user, 'createToken')
            ? $user->createToken($this->guard)->plainTextToken
            : null;

        return ApiResponse::respondWithArray([
            'verified' => true,
            'token' => $token,
        ])->send();
    }
    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        /** @var \Illuminate\Database\Eloquent\Model&\Illuminate\Contracts\Auth\Authenticatable|null $user */
        $user = auth($this->guard)->user();
        if (! $user) {
            return ApiResponse::respondWithError('Unauthenticated.', httpStatus: 401)->send();
        }

        $user->password = $data['password'];
        $user->save();

        return ApiResponse::respondWithSuccess(message: __('Updated Successfully'))->send();
    }
}
