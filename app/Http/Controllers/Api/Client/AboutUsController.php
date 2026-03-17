<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\AboutUsSettingService;

class AboutUsController extends Controller
{
    public function __construct(
        private AboutUsSettingService $aboutUsSettingService
    ) {}

    public function index()
    {
        return ApiResponse::respondWithArray(
            $this->aboutUsSettingService->getLocalizedSettings()
        )->send();
    }
}

