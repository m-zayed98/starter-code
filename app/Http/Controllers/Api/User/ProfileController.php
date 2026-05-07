<?php

namespace App\Http\Controllers\Api\User;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\User\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    protected string $guard = 'api';

    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * List / show the authenticated user's profile.
     */
    public function index(): JsonResponse
    {
        $user = auth($this->guard)->user();
        $profile = $this->userService->getProfile($user->id);

        return ApiResponse::respondWithArray([
            'user' => UserResource::make($profile),
        ], message: __('Profile retrieved successfully'))->send();
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth($this->guard)->user();
        $data = $request->validated();
        $updated = $this->userService->updateProfile($user->id, $data);

        return ApiResponse::respondWithArray([
            'user' => UserResource::make($updated),
        ], message: __('Profile updated successfully'))->send();
    }

    /**
     * Permanently delete the authenticated user's account.
     */
    public function destroy(): JsonResponse
    {
        $user = auth($this->guard)->user();

        if (! $user) {
            return ApiResponse::respondWithError(__('Unauthenticated.'), httpStatus: 401)->send();
        }

        $this->userService->deleteAccount($user->id);

        return ApiResponse::respondWithSuccess(message: __('Account deleted successfully'))->send();
    }
}
