<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\PrivacySettingService;

class PrivacyController extends Controller
{
    public function __construct(
        private PrivacySettingService $privacySettingService
    ) {}

    public function index()
    {
        return ApiResponse::respondWithArray(
            $this->privacySettingService->getSettings()
        )->send();
    }
}

