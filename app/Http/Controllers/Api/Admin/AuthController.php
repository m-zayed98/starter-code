<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseAuthController;
use App\Http\Resources\Admin\AdminResource;
use App\Http\Resources\Admin\PermissionResource;
use App\Models\Admin;
use Illuminate\Http\Request;

class AuthController extends BaseAuthController
{
    protected string $guard = 'admin';

    protected string $authModel = Admin::class;

    protected string $loginKey = 'email';

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

        $user = $result['user'];
        $permissions = $user->getAllPermissions()->pluck('name');
        return ApiResponse::respondWithArray([
            'user' => AdminResource::make($user),
            'token' => $result['token'] ?? null,
            'permissions' => $permissions
        ])->send();
    }
}
