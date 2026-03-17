<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAboutUsSettingRequest;
use App\Services\AboutUsSettingService;

class AboutUsSettingController extends Controller
{
    public function __construct(
        private AboutUsSettingService $aboutUsSettingService
    ) {}

    public function show()
    {
        return ApiResponse::respondWithArray(
            $this->aboutUsSettingService->getSettings()
        )->send();
    }

    public function update(UpdateAboutUsSettingRequest $request)
    {
        $this->aboutUsSettingService->updateSettings($request);

        return ApiResponse::respondWithArray(
            $this->aboutUsSettingService->getSettings(),
            __('Updated successfully')
        )->send();
    }
}

