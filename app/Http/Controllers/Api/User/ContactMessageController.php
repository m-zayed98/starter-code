<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\User\StoreContactMessageRequest;
use App\Http\Requests\User\UpdateContactMessageRequest;
use App\Http\Resources\User\ContactMessageResource;
use App\Services\ContactMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends BaseApiController
{
    /**
     * The service instance.
     *
     * @var ContactMessageService
     */
    protected $service;

    /**
     * Create a new controller instance.
     *
     * @param ContactMessageService $service
     */
    public function __construct(ContactMessageService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
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

        $data = $this->service->get($options);

        return $this->successResponse(
            ContactMessageResource::collection($data),
            'ContactMessage list retrieved successfully'
        );
    }

    /**
     * Store a newly created resource.
     *
     * @param StoreContactMessageRequest $request
     * @return JsonResponse
     */
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $contactMessage = $this->service->create($request->validated());

        return $this->createdResponse(
            new ContactMessageResource($contactMessage),
            'ContactMessage created successfully'
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $options = [
            'withTrashed' => $request->boolean('with_trashed', false),
            'relations' => $request->input('relations', []),
        ];

        $contactMessage = $this->service->showOrFail($id, $options);

        return $this->successResponse(
            new ContactMessageResource($contactMessage),
            'ContactMessage retrieved successfully'
        );
    }

    /**
     * Update the specified resource.
     *
     * @param UpdateContactMessageRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateContactMessageRequest $request, int $id): JsonResponse
    {
        $contactMessage = $this->service->update($id, $request->validated());

        if (!$contactMessage) {
            return $this->notFoundResponse('ContactMessage not found');
        }

        return $this->successResponse(
            new ContactMessageResource($contactMessage),
            'ContactMessage updated successfully'
        );
    }

    /**
     * Remove the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $forceDelete = $request->boolean('force_delete', false);
        $deleted = $this->service->delete($id, $forceDelete);

        if (!$deleted) {
            return $this->notFoundResponse('ContactMessage not found');
        }

        return $this->successResponse(null, 'ContactMessage deleted successfully');
    }

    /**
     * Restore the specified soft-deleted resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $restored = $this->service->restore($id);

        if (!$restored) {
            return $this->notFoundResponse('ContactMessage not found or not soft-deleted');
        }

        return $this->successResponse(null, 'ContactMessage restored successfully');
    }

    /**
     * Delete multiple resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $forceDelete = $request->boolean('force_delete', false);
        $count = $this->service->deleteMultiple($request->input('ids'), $forceDelete);

        return $this->successResponse(
            ['count' => $count],
            "{$count} ContactMessage deleted successfully"
        );
    }
}