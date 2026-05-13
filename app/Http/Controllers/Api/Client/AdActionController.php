<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\AdActionType;
use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreAdActionRequest;
use App\Services\AdActionService;
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
     *
     * Request body:
     *   type: "call" | "whatsapp"
     */
    public function store(StoreAdActionRequest $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $type = AdActionType::from($request->validated('type'));

        // Reject view type — views are tracked automatically on the show endpoint
        if ($type === AdActionType::VIEW) {
            return ApiResponse::respondWithError(
                __('لا يمكن تسجيل إجراء المشاهدة يدوياً.'),
                httpStatus: 422,
            )->send();
        }

        try {
            $this->adActionService->recordAction($id, $user->id, $type);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('الإعلان غير موجود.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithSuccess(
            message: __('تم تسجيل الإجراء بنجاح.'),
        )->send();
    }
}
