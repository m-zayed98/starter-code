<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTermsAndCondiotionsSettingRequest;
use App\Services\TermsAndCondiotionsSettingService;

class TermsAndCondiotionsSettingController extends Controller
{
    public function __construct(
        private TermsAndCondiotionsSettingService $termsSettingService
    ) {}

    public function show()
    {
        return ApiResponse::respondWithArray(
            $this->termsSettingService->getSettings()
        )->send();
    }

    public function update(UpdateTermsAndCondiotionsSettingRequest $request)
    {
        $this->termsSettingService->updateSettings($request);

        return ApiResponse::respondWithArray(
            $this->termsSettingService->getSettings(),
            __('Updated successfully')
        )->send();
    }
}

