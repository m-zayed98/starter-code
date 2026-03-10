<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Services\RoleService;

class RoleController extends BaseApiController
{
    protected string $modelName    = 'Role';
    protected string $serviceName  = RoleService::class;
    protected string $resource     = RoleResource::class;
    protected string $storeRequest = StoreRoleRequest::class;
    protected string $updateRequest = UpdateRoleRequest::class;

    protected bool $usePermissions = true;

    protected array $queryOptions = [
        'index' => ['perPage' => 15, 'applyFilters' => true, 'relations' => ['permissions']],
        'show'  => ['relations' => ['permissions']],
    ];
}