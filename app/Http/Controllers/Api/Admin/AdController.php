<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Admin\AdDetailResource;
use App\Http\Resources\Admin\AdListResource;
use App\Services\AdminAdService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class AdController extends BaseApiController
{
    protected string $modelName = 'Ad';

    protected string $serviceName = AdminAdService::class;

    protected string $resource = AdDetailResource::class;

    protected bool $usePermissions = true;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:ad:read', only: ['index', 'show']),
            new Middleware('permission:ad:update', only: ['toggleStatus']),
        ];
    }

    /**
     * GET /admin/ads
     */
    public function index(Request $request): JsonResponse
    {
        /** @var AdminAdService $service */
        $service = $this->service;

        $ads = $service->listAds(perPage: (int) $request->input('per_page', 15));

        return ApiResponse::respondWithCollection(AdListResource::collection($ads))
            ->withPagination()
            ->send();
    }

    /**
     * GET /admin/ads/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var AdminAdService $service */
        $service = $this->service;

        try {
            $ad = $service->showAd($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Ad not found.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(new AdDetailResource($ad))->send();
    }

    /**
     * PUT /admin/ads/{id}/toggle-status
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        /** @var AdminAdService $service */
        $service = $this->service;

        try {
            $ad = $service->toggleStatus($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Ad not found.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(
            new AdDetailResource($ad),
            message: __('Ad status updated successfully.'),
        )->send();
    }
}
