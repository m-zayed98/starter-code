<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactSettingRequest;
use App\Services\ContactSettingService;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function __construct(
        private ContactSettingService $contactSettingService
    ) {}

    public function show()
    {
        $settings = $this->contactSettingService->getSettings();

        return ApiResponse::respondWithArray($settings)->send();
    }

    public function update(UpdateContactSettingRequest $request)
    {
        $this->contactSettingService->updateSettings($request);

        return ApiResponse::respondWithArray(
            $this->contactSettingService->getSettings(),
            __('Updated successfully')
        )->send();
    }
}
