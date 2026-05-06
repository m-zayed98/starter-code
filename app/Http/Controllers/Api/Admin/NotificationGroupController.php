<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreNotificationGroupRequest;
use App\Http\Resources\Admin\NotificationGroupDetailResource;
use App\Http\Resources\Admin\NotificationGroupResource;
use App\Services\NotificationGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationGroupController extends BaseApiController
{
    protected string $modelName = 'NotificationGroup';
    protected string $serviceName = NotificationGroupService::class;
    protected string $resource = NotificationGroupResource::class;
    protected string $storeRequest = StoreNotificationGroupRequest::class;

    protected bool $usePermissions = false;

    protected array $queryOptions = [
        'index' => ['perPage' => 15, 'applyFilters' => true],
        'show'  => ['relations' => []],
    ];

    public function store(Request $request): JsonResponse
    {
        $data = $this->resolveFormRequest($this->storeRequest)->validated();
        
        $data['created_by'] = auth('admin')->id();

        /** @var NotificationGroupService $service */
        $service = $this->service;
        $group = $service->createAndSend($data);

        return ApiResponse::respondWithModel(
            new NotificationGroupResource($group),
            message: __('Notification sent successfully.'),
            httpStatus: 201
        )->send();
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $model = $this->service->showOrFail($id, $this->resolveQueryOptions('show'));

        return ApiResponse::respondWithModel(
            new NotificationGroupDetailResource($model)
        )->send();
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
        } catch (\DomainException $e) {
            return ApiResponse::respondWithError(
                __($e->getMessage()),
                httpStatus: 422
            )->send();
        }

        return ApiResponse::respondWithSuccess(
            message: __('Deleted successfully')
        )->send();
    }

    private function resolveFormRequest(string $requestClass): Request
    {
        if ($requestClass === '') {
            return request();
        }

        return app($requestClass);
    }

    private function resolveQueryOptions(string $action): \App\Repositories\DTOs\QueryOptions
    {
        return \App\Repositories\DTOs\QueryOptions::make($this->queryOptions[$action] ?? []);
    }
}
