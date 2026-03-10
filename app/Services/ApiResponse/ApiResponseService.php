<?php

namespace App\Services\ApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  ApiResponseService                                                     │
 * │                                                                         │
 * │  Builds standardised JSON API responses around Laravel API Resources.   │
 * │                                                                         │
 * │  Response envelope:                                                     │
 * │  {                                                                      │
 * │    "message":     string,                                               │
 * │    "status_code": string  (numeric string, e.g. "1000"),                │
 * │    "data":        mixed   (null for errors),                            │
 * │    "errors":      object|null                                           │
 * │  }                                                                      │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * Usage:
 *
 *   // Collection (non-paginated)
 *   return ApiResponse::respondWithCollection(UserResource::collection($users))->send();
 *
 *   // Collection (paginated)
 *   return ApiResponse::respondWithCollection(UserResource::collection($users))
 *       ->withPagination()
 *       ->mergeAdditional(['some_key' => 'value'])
 *       ->send();
 *
 *   // Single model
 *   return ApiResponse::respondWithModel(new UserResource($user))->send();
 *
 *   // Custom array
 *   return ApiResponse::respondWithArray(['token' => $token])->send();
 *
 *   // Error
 *   return ApiResponse::respondWithError('Not found.', StatusCode::NOT_FOUND, 404)->send();
 */
class ApiResponseService
{
    private string  $message    = 'success';
    private string  $statusCode = StatusCode::SUCCESS;
    private int     $httpStatus = 200;
    private ?array  $errors     = null;
    private array   $additional = [];
    private bool    $paginate   = false;

    private ?ResourceCollection $collection = null;
    private ?JsonResource       $resource   = null;
    private ?array              $rawData    = null;

    // ══════════════════════════════════════════════════════════════════════
    //  Entry points
    // ══════════════════════════════════════════════════════════════════════

    /**
     * For index endpoints. Pass the result of Resource::collection($items).
     * Chain ->withPagination() if $items is a paginator.
     */
    public function respondWithCollection(
        ResourceCollection $collection,
        string $message    = 'success',
        string $statusCode = StatusCode::SUCCESS,
        int    $httpStatus = 200,
    ): static {
        $this->message    = $message;
        $this->statusCode = $statusCode;
        $this->httpStatus = $httpStatus;
        $this->collection = $collection;

        return $this;
    }

    /**
     * For show endpoints. Pass a single JsonResource instance.
     */
    public function respondWithModel(
        JsonResource $resource,
        string $message    = 'success',
        string $statusCode = StatusCode::SUCCESS,
        int    $httpStatus = 200,
    ): static {
        $this->message    = $message;
        $this->statusCode = $statusCode;
        $this->httpStatus = $httpStatus;
        $this->resource   = $resource;

        return $this;
    }

    /**
     * For custom responses (e.g. tokens, counts). Array used as-is for data.
     *
     * @param  array<string, mixed>  $data
     */
    public function respondWithArray(
        array  $data,
        string $message    = 'success',
        string $statusCode = StatusCode::SUCCESS,
        int    $httpStatus = 200,
    ): static {
        $this->message    = $message;
        $this->statusCode = $statusCode;
        $this->httpStatus = $httpStatus;
        $this->rawData    = $data;

        return $this;
    }

    /**
     * For error responses. Pass structured $errors for 400/422 responses.
     *
     * @param  array<string, string[]>|null  $errors
     */
    public function respondWithError(
        string $message,
        string $statusCode = StatusCode::SERVER_ERROR,
        int    $httpStatus = 500,
        ?array $errors     = null,
    ): static {
        $this->message    = $message;
        $this->statusCode = $statusCode;
        $this->httpStatus = $httpStatus;

        if ($errors !== null && in_array($httpStatus, [400, 422], strict: true)) {
            $this->errors = $errors;
        }

        return $this;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Fluent modifiers
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Mark a ResourceCollection as paginated.
     *
     * When set, ->response()->getData(true) is called on the collection,
     * letting Laravel produce its native data/links/meta envelope.
     * Must be used after respondWithCollection().
     */
    public function withPagination(): static
    {
        $this->paginate = true;

        return $this;
    }

    /**
     * Merge extra keys into the data payload.
     *
     * @param  array<string, mixed>  $additional
     */
    public function mergeAdditional(array $additional): static
    {
        $this->additional = array_merge($this->additional, $additional);

        return $this;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Terminal
    // ══════════════════════════════════════════════════════════════════════

    public function send(): JsonResponse
    {
        return response()->json([
            'message'     => $this->message,
            'status_code' => $this->statusCode,
            'data'        => $this->buildData(),
            'errors'      => $this->errors,
        ], $this->httpStatus);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Internal
    // ══════════════════════════════════════════════════════════════════════

    private function buildData(): mixed
    {
        // ── ResourceCollection ────────────────────────────────────────────
        if ($this->collection !== null) {
            $resolved = $this->paginate
                ? $this->collection->response()->getData(true)  // { data, links, meta }
                : $this->collection->resolve();                  // plain items array

            return empty($this->additional)
                ? $resolved
                : array_merge((array) $resolved, $this->additional);
        }

        // ── Single JsonResource ───────────────────────────────────────────
        if ($this->resource !== null) {
            $resolved = $this->resource->resolve();

            return empty($this->additional)
                ? $resolved
                : array_merge($resolved, $this->additional);
        }

        // ── Raw array ─────────────────────────────────────────────────────
        if ($this->rawData !== null) {
            return empty($this->additional)
                ? $this->rawData
                : array_merge($this->rawData, $this->additional);
        }

        // ── Error / no data ───────────────────────────────────────────────
        return [];
    }
}
