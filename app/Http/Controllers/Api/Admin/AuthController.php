<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseAuthController;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Models\Admin;
use App\Services\Auth\AuthAdminService;

class AuthController extends BaseAuthController
{
    protected string $guard = 'admin';

    protected string $authModel = Admin::class;

    protected string $loginKey = 'email';

    protected $loginFormRequest = AdminLoginRequest::class;

    protected $authService = AuthAdminService::class;
}
