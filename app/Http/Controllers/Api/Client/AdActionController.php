<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\AdActionType;
use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreAdActionRequest;
use App\Models\User;
use App\Services\AdActionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class AdActionController extends Controller
{
    public function __construct(
        private readonly AdActionService $adActionService,
    ) {}

    /**
     * POST /public/ads/{id}/action
     *
     * Record a call or whatsapp interaction on a published ad.
     * Requires authentication. Deduplicated per user per ad per type.
     */
    public function store(StoreAdActionRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        $type = AdActionType::from($request->validated('type'));

        if ($type === AdActionType::VIEW) {
            return ApiResponse::respondWithError(
                __('View action cannot be recorded manually.'),
                httpStatus: 422,
            )->send();
        }

        try {
            $this->adActionService->recordAction($id, $user->id, $type);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Ad not found.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithSuccess(
            message: __('Action recorded successfully.'),
        )->send();
    }
}
