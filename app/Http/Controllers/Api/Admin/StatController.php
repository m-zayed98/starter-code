<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\AdActionService;
use Illuminate\Http\JsonResponse;

class StatController extends Controller
{
    public function __construct(
        private readonly AdActionService $adActionService,
    ) {}

    /**
     * GET /admin/stats
     *
     * Returns dashboard statistics for the admin panel:
     *  - active_ads_count          : number of published ads
     *  - inactive_ads_count        : number of non-published ads
     *  - total_users_count         : total registered users
     *  - advertiser_users_count    : users who have at least one ad
     *  - call_rate                 : (total call actions / total ads) * 100  [%]
     *  - view_rate                 : (total view actions / total ads) * 100  [%]
     *  - whatsapp_rate             : (total whatsapp actions / total ads) * 100  [%]
     */
    public function index(): JsonResponse
    {
        $stats = $this->adActionService->getAdminStats();

        return ApiResponse::respondWithArray($stats)->send();
    }
}
