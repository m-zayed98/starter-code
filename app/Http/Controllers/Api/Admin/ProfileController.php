<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseAuthController;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\AdminService;
use Illuminate\Http\Request;

class ProfileController extends BaseAuthController
{
    protected string $guard = 'admin';

    protected string $authModel = \App\Models\Admin::class;

    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Update the authenticated admin's profile.
     *
     * @param UpdateProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProfileRequest $request)
    {
        $admin = auth($this->guard)->user();

        $data = $request->validated();

        $updatedAdmin = $this->adminService->updateProfile($admin->id, $data);

        return ApiResponse::respondWithArray([
            'user' => AdminResource::make($updatedAdmin),
        ], message: __('Profile updated successfully'))->send();
    }

    /**
     * Get the authenticated admin's profile data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $admin = auth($this->guard)->user();

        return ApiResponse::respondWithArray([
            'user' => AdminResource::make($admin),
        ], message: __('Profile retrieved successfully'))->send();
    }
}
