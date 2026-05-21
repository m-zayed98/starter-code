<?php

namespace App\Http\Controllers\Api\User;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\InitiateAdRequest;
use App\Http\Requests\User\UpdateAdRequest;
use App\Http\Resources\User\AdResource;
use App\Models\User;
use App\Services\AdService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class AdController extends Controller
{
    public function __construct(
        private readonly AdService $adService,
    ) {}

    /**
     * GET /ads
     */
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        $ads = $this->adService->listUserAds($user->id);

        return ApiResponse::respondWithCollection(AdResource::collection($ads))
            ->withPagination($ads)
            ->send();
    }

    /**
     * POST /ads — Step 1: Initiate ad creation.
     */
    public function store(InitiateAdRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        try {
            $ad = $this->adService->initiateAd($user->id, $request->validated());
        } catch (\DomainException $e) {
            return ApiResponse::respondWithError(
                $e->getMessage(),
                httpStatus: 422,
            )->send();
        }

        return ApiResponse::respondWithModel(
            new AdResource($ad),
            message: __('Ad created successfully. Please complete the remaining details.'),
            httpStatus: 201,
        )->send();
    }

    /**
     * GET /ads/{id}
     */
    public function show(int $id): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        try {
            $ad = $this->adService->showUserAd($id, $user->id);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Ad not found.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(new AdResource($ad))->send();
    }

    /**
     * PUT /ads/{id} — Steps 2, 3 & 4: Update draft ad.
     */
    public function update(UpdateAdRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        try {
            $ad = $this->adService->updateAd($id, $user->id, $request->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Ad not found.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(
            new AdResource($ad),
            message: __('Ad updated successfully.'),
        )->send();
    }

    /**
     * DELETE /ads/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        try {
            $this->adService->deleteAd($id, $user->id);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Ad not found.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithSuccess(
            message: __('Ad deleted successfully.'),
        )->send();
    }

    /**
     * PUT /ads/{id}/toggle-status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        try {
            $ad = $this->adService->toggleStatus($id, $user->id);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Ad not found.'),
                httpStatus: 404,
            )->send();
        } catch (\DomainException $e) {
            return ApiResponse::respondWithError(
                $e->getMessage(),
                httpStatus: 422,
            )->send();
        }

        return ApiResponse::respondWithModel(
            new AdResource($ad),
            message: __('Ad status updated successfully.'),
        )->send();
    }

    /**
     * GET /ads/stats
     */
    public function stats(): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        $stats = $this->adService->getUserAdStats($user->id);

        return ApiResponse::respondWithArray($stats)->send();
    }
}
