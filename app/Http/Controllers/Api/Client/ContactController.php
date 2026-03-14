<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\ContactSettingService;

class ContactController extends Controller
{
    public function __construct(
        private ContactSettingService $contactSettingService
    ) {}

    public function index()
    {
        $settings = $this->contactSettingService->getSettings();
        return ApiResponse::respondWithArray($settings)->send();
    }
}
