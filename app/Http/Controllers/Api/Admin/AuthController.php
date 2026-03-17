<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseAuthController;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\LogoutRequest;
use App\Models\Admin;
use App\Services\Auth\AuthAdminService;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseAuthController
{
    protected string $guard = 'admin';

    protected string $authModel = Admin::class;

    protected string $loginKey = 'email';

    protected $loginFormRequest = AdminLoginRequest::class;

    protected $authService = AuthAdminService::class;

    public function logout(LogoutRequest $request): JsonResponse
    {
        $admin = auth($this->guard)->user();
        if (! $admin) {
            return ApiResponse::respondWithError('Unauthenticated.', httpStatus: 401)->send();
        }

        $data = $request->validated();
        $token = $admin->currentAccessToken();
        $token?->delete();

        if (! empty($data['device_token'])) {
            // TODO: remove device token association (persistence not implemented yet).
        }

        return ApiResponse::respondWithSuccess(message: __('Logged out successfully'))->send();
    }
}
