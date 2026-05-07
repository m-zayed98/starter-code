<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreAdReportRequest;
use App\Http\Requests\Client\StoreAdReviewRequest;
use App\Http\Resources\Client\AdDetailResource;
use App\Http\Resources\Client\AdListResource;
use App\Http\Resources\Client\AdReportResource;
use App\Http\Resources\Client\AdReviewResource;
use App\Services\PublicAdService;
use Illuminate\Http\JsonResponse;

class AdController extends Controller
{
    public function __construct(
        private readonly PublicAdService $publicAdService,
    ) {}

    /**
     * GET /public/ads
     *
     * Public paginated listing of published ads.
     * Accessible by guests and authenticated users.
     *
     * Supported query params (all optional):
     *   search, purpose, apartment_condition, rental_period,
     *   furnishing_status, price_min, price_max,
     *   property_type, region, city, district
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
     *
     * Full detail view of a single published ad.
     * Accessible by guests and authenticated users.
     * Sensitive contact fields (nhc_mobile, advertiser_phone) are hidden for guests.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $ad = $this->publicAdService->showPublishedAd($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('الإعلان غير موجود.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(new AdDetailResource($ad))->send();
    }

    /**
     * POST /public/ads/{id}/reviews
     *
     * Submit a star rating + optional feedback for a published ad.
     * Requires authentication. One review per user per ad.
     */
    public function storeReview(StoreAdReviewRequest $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        try {
            $review = $this->publicAdService->submitReview(
                adId:     $id,
                userId:   $user->id,
                rating:   $request->validated('rating'),
                feedback: $request->validated('feedback'),
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('الإعلان غير موجود.'),
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
            message: __('تم إرسال تقييمك بنجاح.'),
            httpStatus: 201,
        )->send();
    }

    /**
     * POST /public/ads/{id}/reports
     *
     * Report a published ad with a written reason.
     * Requires authentication. One report per user per ad.
     */
    public function storeReport(StoreAdReportRequest $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        try {
            $report = $this->publicAdService->submitReport(
                adId:   $id,
                userId: $user->id,
                reason: $request->validated('reason'),
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('الإعلان غير موجود.'),
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
            message: __('تم إرسال بلاغك بنجاح.'),
            httpStatus: 201,
        )->send();
    }
}
