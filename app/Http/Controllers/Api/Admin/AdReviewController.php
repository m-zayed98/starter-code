<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Admin\AdReviewListResource;
use App\Services\AdminAdReviewService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class AdReviewController extends BaseApiController
{
    protected string $modelName = 'AdReview';

    protected string $serviceName = AdminAdReviewService::class;

    protected string $resource = AdReviewListResource::class;

    protected bool $usePermissions = true;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:ad_review:read', only: ['index']),
            new Middleware('permission:ad_review:update', only: ['toggleVisibility']),
        ];
    }

    /**
     * GET /admin/ad-reviews
     */
    public function index(Request $request): JsonResponse
    {
        /** @var AdminAdReviewService $service */
        $service = $this->service;

        $reviews = $service->listReviews(perPage: (int) $request->input('per_page', 15));

        return ApiResponse::respondWithCollection(AdReviewListResource::collection($reviews))
            ->withPagination()
            ->send();
    }

    /**
     * PUT /admin/ad-reviews/{id}/toggle-visibility
     */
    public function toggleVisibility(Request $request, int $id): JsonResponse
    {
        /** @var AdminAdReviewService $service */
        $service = $this->service;

        try {
            $review = $service->toggleVisibility($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Comment not found.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(
            new AdReviewListResource($review),
            message: __('Comment status updated successfully.'),
        )->send();
    }
}
