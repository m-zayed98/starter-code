<?php

namespace App\Facades;

use App\Services\ApiResponse\ApiResponseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Facade;

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  ApiResponse  –  Laravel Facade                                         │
 * │                                                                         │
 * │  Static proxy to ApiResponseService.                                    │
 * │  Every static call resolves a FRESH instance so builder chains are      │
 * │  fully isolated between requests.                                        │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * Usage:
 *
 *   use App\Facades\ApiResponse;
 *   use App\Services\ApiResponse\StatusCode;
 *
 *   // Index (collection)
 *   return ApiResponse::respondWithCollection(UserResource::collection($users))
 *       ->withPagination($users)
 *       ->mergeAdditional(['total_active' => 42])
 *       ->send();
 *
 *   // Show (single model)
 *   return ApiResponse::respondWithModel(new UserResource($user))->send();
 *
 *   // Custom array
 *   return ApiResponse::respondWithArray(['token' => $token])->send();
 *
 *   // Error with validation errors
 *   return ApiResponse::respondWithError(
 *       'Validation failed.',
 *       StatusCode::VALIDATION_ERROR,
 *       422,
 *       $validator->errors()->toArray()
 *   )->send();
 *
 * @method static \App\Services\ApiResponse\ApiResponseService respondWithCollection(\Illuminate\Http\Resources\Json\ResourceCollection $collection, string $message = 'success', string $statusCode = \App\Services\ApiResponse\StatusCode::SUCCESS, int $httpStatus = 200)
 * @method static \App\Services\ApiResponse\ApiResponseService respondWithModel(\Illuminate\Http\Resources\Json\JsonResource $resource, string $message = 'success', string $statusCode = \App\Services\ApiResponse\StatusCode::SUCCESS, int $httpStatus = 200)
 * @method static \App\Services\ApiResponse\ApiResponseService respondWithArray(array $data, string $message = 'success', string $statusCode = \App\Services\ApiResponse\StatusCode::SUCCESS, int $httpStatus = 200)
 * @method static \App\Services\ApiResponse\ApiResponseService respondWithError(string $message, string $statusCode = \App\Services\ApiResponse\StatusCode::SERVER_ERROR, int $httpStatus = 500, ?array $errors = [])
 * @method static \App\Services\ApiResponse\ApiResponseService withPagination()
 * @method static \App\Services\ApiResponse\ApiResponseService mergeAdditional(array $additional)
 * @method static \Illuminate\Http\JsonResponse send()
 *
 * @see \App\Services\ApiResponse\ApiResponseService
 */
final class ApiResponse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApiResponseService::class;
    }

    /**
     * Always resolve a FRESH instance so no builder state leaks between calls.
     */
    protected static function resolveFacadeInstance($name): ApiResponseService
    {
        return static::$app->make($name);
    }
}
