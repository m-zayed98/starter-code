<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreAdReportRequest;
use App\Http\Requests\Client\StoreAdReviewRequest;
use App\Http\Resources\Client\AdDetailResource;
use App\Http\Resources\Client\AdListResource;
use App\Http\Resources\Client\AdMapResource;
use App\Http\Resources\Client\AdReportResource;
use App\Http\Resources\Client\AdReviewResource;
use App\Models\User;
use App\Services\PublicAdService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class AdController extends Controller
{
    public function __construct(
        private readonly PublicAdService $publicAdService,
    ) {}

    /**
     * GET /public/ads/map
     */
    public function map(): JsonResponse
    {
        $ads = $this->publicAdService->listPublishedAdsForMap(perPage: 200);

        return ApiResponse::respondWithCollection(AdMapResource::collection($ads))
            ->withPagination($ads)
            ->send();
    }

    /**
     * GET /public/ads
     */
    public function index(): JsonResponse
    {
        $ads = $this->publicAdService->listPublishedAds(perPage: 10);

        return ApiResponse::respondWithCollection(AdListResource::collection($ads))
            ->withPagination($ads)
            ->send();
    }

    /**
     * GET /public/ads/{id}
     */
    public function show(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = auth('api')->user();

        try {
            $ad = $this->publicAdService->showPublishedAd($id, $user?->id);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Ad not found.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(new AdDetailResource($ad))->send();
    }

    /**
     * POST /public/ads/{id}/reviews
     */
    public function storeReview(StoreAdReviewRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        try {
            $review = $this->publicAdService->submitReview(
                adId: $id,
                userId: $user->id,
                rating: $request->validated('rating'),
                feedback: $request->validated('feedback'),
            );
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
            new AdReviewResource($review),
            message: __('Your review has been submitted successfully.'),
            httpStatus: 201,
        )->send();
    }

    /**
     * POST /public/ads/{id}/reports
     */
    public function storeReport(StoreAdReportRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        try {
            $report = $this->publicAdService->submitReport(
                adId: $id,
                userId: $user->id,
                reason: $request->validated('reason'),
            );
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
            new AdReportResource($report),
            message: __('Your report has been submitted successfully.'),
            httpStatus: 201,
        )->send();
    }
}
