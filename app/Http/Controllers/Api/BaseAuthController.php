<?php

namespace App\Http\Controllers\Api;

use App\Auth\Login\LoginStrategy;
use App\Enums\LoginStrategyType;
use App\Enums\OtpPurpose;
use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use InvalidArgumentException;

abstract class BaseAuthController extends Controller
{
    /**
     * Default login strategy key for the login() method.
     * Child controllers may override this constant.
     */
    protected const LOGIN_STRATEGY = LoginStrategyType::PASSWORD;

    /**
     * Guard name used for authentication (e.g. 'api', 'admin').
     */
    protected string $guard = 'api';

    /**
     * FQCN of the authenticatable Eloquent model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model&\Illuminate\Contracts\Auth\Authenticatable>
     */
    protected string $authModel;

    /**
     * Column used for login (e.g. 'email', 'phone').
     */
    protected string $loginKey = 'email';

    /**
     * Email/password login.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            $this->loginKey => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $strategy = static::LOGIN_STRATEGY->make();

        $result = $strategy->login(
            $this->guard,
            $this->loginKey,
            $this->authModel,
            $data
        );

        return ApiResponse::respondWithArray([
            'user' => $result['user'],
            'token' => $result['token'] ?? null,
        ])->send();
    }

    /**
     * Start "forgot password" flow by issuing an OTP.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            $this->loginKey => ['required', 'string'],
        ]);

        $strategy = LoginStrategyType::OTP->make([
            'purpose' => OtpPurpose::FORGOT_PASSWORD->value,
        ]);

        $result = $strategy->login(
            $this->guard,
            $this->loginKey,
            $this->authModel,
            $data
        );

        // In production you would send the OTP via email/SMS instead of returning it.
        return ApiResponse::respondWithArray([
            'message' => 'OTP generated successfully.',
            'expires_at' => $result['expires_at'],
            'purpose' => $result['purpose'],
            'otp' => $result['otp']->code,
        ])->send();
    }

    /**
     * Verify an OTP for a given purpose (e.g. reset password).
     */
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

        $valid = $user->consumeOtp($purpose->value, $data['code']);

        if (! $valid) {
            return ApiResponse::respondWithError('Invalid or expired OTP.', httpStatus: 422)->send();
        }

        return ApiResponse::respondWithArray([
            'verified' => true,
            'purpose' => $purpose->value,
        ])->send();
    }

    /**
     * Reset password using a valid OTP for the "forgot password" purpose.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            $this->loginKey => ['required', 'string'],
            'code' => ['required', 'string'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        /** @var \Illuminate\Contracts\Auth\Authenticatable&\App\Traits\HasOtps|null $user */
        $user = ($this->authModel)::query()
            ->where($this->loginKey, $data[$this->loginKey])
            ->first();

        if (! $user || ! method_exists($user, 'consumeOtp')) {
            return ApiResponse::respondWithError('Invalid user.', httpStatus: 404)->send();
        }

        $valid = $user->consumeOtp(OtpPurpose::FORGOT_PASSWORD->value, $data['code']);

        if (! $valid) {
            return ApiResponse::respondWithError('Invalid or expired OTP.', httpStatus: 422)->send();
        }

        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        return ApiResponse::respondWithArray([
            'message' => 'Password reset successfully.',
        ])->send();
    }
}
