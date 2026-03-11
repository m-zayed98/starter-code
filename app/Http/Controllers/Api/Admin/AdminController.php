<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends BaseApiController
{
    /**
     * The service instance.
     *
     * @var AdminService
     */
    protected $service;

    /**
     * Create a new controller instance.
     *
     * @param AdminService $service
     */
    public function __construct(AdminService $service)
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
            AdminResource::collection($data),
            'Admin list retrieved successfully'
        );
    }

    /**
     * Store a newly created resource.
     *
     * @param StoreAdminRequest $request
     * @return JsonResponse
     */
    public function store(StoreAdminRequest $request): JsonResponse
    {
        $admin = $this->service->create($request->validated());

        return $this->createdResponse(
            new AdminResource($admin),
            'Admin created successfully'
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

        $admin = $this->service->showOrFail($id, $options);

        return $this->successResponse(
            new AdminResource($admin),
            'Admin retrieved successfully'
        );
    }

    /**
     * Update the specified resource.
     *
     * @param UpdateAdminRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateAdminRequest $request, int $id): JsonResponse
    {
        $admin = $this->service->update($id, $request->validated());

        if (!$admin) {
            return $this->notFoundResponse('Admin not found');
        }

        return $this->successResponse(
            new AdminResource($admin),
            'Admin updated successfully'
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
            return $this->notFoundResponse('Admin not found');
        }

        return $this->successResponse(null, 'Admin deleted successfully');
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
            return $this->notFoundResponse('Admin not found or not soft-deleted');
        }

        return $this->successResponse(null, 'Admin restored successfully');
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
            "{$count} Admin deleted successfully"
        );
    }
}