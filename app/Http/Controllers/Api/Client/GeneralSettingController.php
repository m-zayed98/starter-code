<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\GeneralSettingService;

class GeneralSettingController extends Controller
{
    public function __construct(
        private GeneralSettingService $generalSettingService
    ) {}

    public function index()
    {
        return ApiResponse::respondWithArray(
            $this->generalSettingService->getPublicSettings()
        )->send();
    }
}
