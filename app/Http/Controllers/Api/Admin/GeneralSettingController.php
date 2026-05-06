<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGeneralSettingRequest;
use App\Services\GeneralSettingService;

class GeneralSettingController extends Controller
{
    public function __construct(
        private GeneralSettingService $generalSettingService
    ) {}

    public function show()
    {
        return ApiResponse::respondWithArray(
            $this->generalSettingService->getSettings()
        )->send();
    }

    public function update(UpdateGeneralSettingRequest $request)
    {
        $this->generalSettingService->updateSettings($request);

        return ApiResponse::respondWithArray(
            $this->generalSettingService->getSettings(),
            __('Updated successfully')
        )->send();
    }
}
