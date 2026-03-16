<?php

namespace App\Services\Auth;

use App\Auth\Login\UsingPasswordLoginStrategy;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin;
use App\Services\Auth\Contracts\AuthLoginServiceContract;

class AuthAdminService implements AuthLoginServiceContract
{
    protected string $guard = 'admin';

    protected string $authModel = Admin::class;

    /**
     * Attempt login for the admin entity. Returns full response data for ApiResponse.
     *
     * @param array<string, mixed> $credentials Validated credentials (must include auth key and 'password')
     * @return array<string, mixed> Response data for ApiResponse::respondWithArray()
     */
    public function login(array $credentials, string $authKey): array
    {
        $strategy = app(UsingPasswordLoginStrategy::class);

        $result = $strategy->login(
            $this->guard,
            $authKey,
            $this->authModel,
            $credentials
        );

        $user = $result['user'];
        $permissions = $user->getAllPermissions()->pluck('name');

        return [
            'user' => AdminResource::make($user),
            'token' => $result['token'] ?? null,
            'permissions' => $permissions,
        ];
    }
}
