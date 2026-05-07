<?php

namespace App\Http\Controllers\Api\User;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\InitiateAdRequest;
use App\Http\Requests\User\UpdateAdRequest;
use App\Http\Resources\User\AdResource;
use App\Services\AdService;
use Illuminate\Http\JsonResponse;

class AdController extends Controller
{
    public function __construct(
        private readonly AdService $adService,
    ) {}

    /**
     * GET /ads
     *
     * Return a paginated list of the authenticated user's ads.
     */
    public function index(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $ads = $this->adService->listUserAds($user->id);

        return ApiResponse::respondWithCollection(AdResource::collection($ads))
            ->withPagination($ads)
            ->send();
    }

    /**
     * POST /ads
     *
     * Step 1 – Initiate ad creation.
     *
     * Validates FAL / NHC advertiser data, saves profile fields to the user,
     * calls NHC (mock) to fetch property data, and creates the ad in DRAFT status.
     *
     * Returns the newly created ad with NHC data so the client can proceed
     * to steps 2, 3, and 4.
     */
    public function store(InitiateAdRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
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
            message: __('تم إنشاء الإعلان بنجاح. يرجى إكمال البيانات المتبقية.'),
            httpStatus: 201,
        )->send();
    }

    /**
     * GET /ads/{id}
     *
     * Return a single ad owned by the authenticated user.
     */
    public function show(int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        try {
            $ad = $this->adService->showUserAd($id, $user->id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('الإعلان غير موجود.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(new AdResource($ad))->send();
    }

    /**
     * PUT /ads/{id}
     *
     * Steps 2, 3 & 4 – Update an existing draft ad with the remaining fields.
     *
     * Accepts all user-editable fields (ad details, apartment specs, media).
     * Publishes the ad once all required fields are provided.
     */
    public function update(UpdateAdRequest $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        try {
            $ad = $this->adService->updateAd($id, $user->id, $request->validated());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('الإعلان غير موجود.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(
            new AdResource($ad),
            message: __('تم تحديث الإعلان بنجاح.'),
        )->send();
    }

    /**
     * DELETE /ads/{id}
     *
     * Delete an ad owned by the authenticated user.
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        try {
            $this->adService->deleteAd($id, $user->id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('الإعلان غير موجود.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithSuccess(
            message: __('تم حذف الإعلان بنجاح.'),
        )->send();
    }
}
