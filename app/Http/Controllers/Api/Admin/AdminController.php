<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\AdminService;

class AdminController extends BaseApiController
{
    protected string $modelName  = 'Admin';
    protected string $serviceName  = AdminService::class;
    protected string $resource     = AdminResource::class;
    protected string $storeRequest = StoreAdminRequest::class;
    protected string $updateRequest = UpdateAdminRequest::class;

    protected bool $usePermissions = true;

    protected array $queryOptions = [
        'index' => ['perPage' => 15, 'applyFilters' => true, 'relations' => ['roles']],
        'show'  => ['relations' => ['roles']],
    ];
}
