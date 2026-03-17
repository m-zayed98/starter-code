<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\UsersExport;
use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\ToggleUserStatusRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends BaseApiController
{
    protected string $modelName = 'User';
    protected string $serviceName = UserService::class;
    protected string $resource = UserResource::class;

    protected bool $usePermissions = true;

    protected array $queryOptions = [
        'index' => ['perPage' => 15, 'applyFilters' => true],
    ];

    public function toggleUserStatus(ToggleUserStatusRequest $request, User $user): JsonResponse
    {
        $reason = $request->input('reason');

        try {
            /** @var UserService $service */
            $service = $this->service;
            $user = $service->toggleStatus($user, $reason);
        } catch (\DomainException) {
            return ApiResponse::respondWithError(
                __('User has upcoming or ongoing bookings and cannot be disabled.'),
                httpStatus: 422
            )->send();
        }

        return ApiResponse::respondWithModel(
            new UserResource($user),
            message: __('Updated successfully')
        )->send();
    }

    public function exportExcel()
    {
        $result = $this->service->get([
            'perPage' => null,
            'applyFilters' => true,
            'orderBy' => request()->input('order_by', 'created_at'),
            'orderDirection' => request()->input('order_direction', 'desc'),
        ]);

        $users = $result instanceof LengthAwarePaginator
            ? $result->getCollection()
            : $result;

        return Excel::download(new UsersExport($users), 'users.xlsx');
    }

    public function exportPdf(): JsonResponse
    {
        return ApiResponse::respondWithError(
            __('PDF export is not available because no PDF package is installed.'),
            httpStatus: 501
        )->send();
    }
}
