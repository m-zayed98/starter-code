<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\TermsAndCondiotionsSettingService;

class TermsAndCondiotionsController extends Controller
{
    public function __construct(
        private TermsAndCondiotionsSettingService $termsSettingService
    ) {}

    public function index()
    {
        return ApiResponse::respondWithArray(
            $this->termsSettingService->getLocalizedSettings()
        )->send();
    }
}

