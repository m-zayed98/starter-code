<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreAdPackageRequest;
use App\Http\Requests\Admin\UpdateAdPackageRequest;
use App\Http\Resources\Admin\AdPackageDetailResource;
use App\Services\AdPackageService;
use Illuminate\Http\JsonResponse;

class AdPackageController extends BaseApiController
{
    protected string $modelName     = 'AdPackage';
    protected string $serviceName   = AdPackageService::class;
    protected string $resource      = AdPackageDetailResource::class;
    protected string $storeRequest  = StoreAdPackageRequest::class;
    protected string $updateRequest = UpdateAdPackageRequest::class;

    protected bool $usePermissions = true;

    protected array $queryOptions = [
        'index' => ['perPage' => 15, 'applyFilters' => true],
    ];
}
