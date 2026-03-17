<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyContactMessageRequest;
use App\Http\Resources\Admin\ContactMessageResource;
use App\Services\ContactMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function __construct(
        private ContactMessageService $contactMessageService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $options = [
            'perPage' => $request->boolean('paginate', true) ? $request->input('per_page', 15) : null,
            'withTrashed' => $request->boolean('with_trashed', false),
            'relations' => $request->input('relations', []),
            'applyFilters' => true,
            'orderBy' => $request->input('order_by', 'created_at'),
            'orderDirection' => $request->input('order_direction', 'desc'),
        ];

        $data = $this->contactMessageService->get($options);

        $response = ApiResponse::respondWithCollection(ContactMessageResource::collection($data));
        if ($request->boolean('paginate', true)) {
            $response->withPagination();
        }

        return $response->send();
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $options = [
            'withTrashed' => $request->boolean('with_trashed', false),
            'relations' => $request->input('relations', []),
        ];

        $contactMessage = $this->contactMessageService->showOrFail($id, $options);

        return ApiResponse::respondWithModel(new ContactMessageResource($contactMessage))->send();
    }

    public function reply(ReplyContactMessageRequest $request, int $id): JsonResponse
    {
        $contactMessage = $this->contactMessageService->reply($id, $request->input('reply'));

        return ApiResponse::respondWithModel(
            new ContactMessageResource($contactMessage),
            message: __('Updated successfully')
        )->send();
    }
}