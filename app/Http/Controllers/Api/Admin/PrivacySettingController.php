<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePrivacySettingRequest;
use App\Services\PrivacySettingService;

class PrivacySettingController extends Controller
{
    public function __construct(
        private PrivacySettingService $privacySettingService
    ) {}

    public function show()
    {
        return ApiResponse::respondWithArray(
            $this->privacySettingService->getSettings()
        )->send();
    }

    public function update(UpdatePrivacySettingRequest $request)
    {
        $this->privacySettingService->updateSettings($request);

        return ApiResponse::respondWithArray(
            $this->privacySettingService->getSettings(),
            __('Updated successfully')
        )->send();
    }
}

