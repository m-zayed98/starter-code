<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\ReplyAdReportRequest;
use App\Http\Resources\Admin\AdReportDetailResource;
use App\Http\Resources\Admin\AdReportListResource;
use App\Services\AdminAdReportService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class AdReportController extends BaseApiController
{
    protected string $modelName = 'AdReport';

    protected string $serviceName = AdminAdReportService::class;

    protected string $resource = AdReportDetailResource::class;

    protected bool $usePermissions = true;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:ad_report:read', only: ['index', 'show']),
            new Middleware('permission:ad_report:update', only: ['reply']),
        ];
    }

    /**
     * GET /admin/ad-reports
     *
     * Paginated list of all ad reports.
     * Supports search by user name/phone, ad_name, and status filter.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var AdminAdReportService $service */
        $service = $this->service;

        $reports = $service->listReports(perPage: (int) $request->input('per_page', 15));

        return ApiResponse::respondWithCollection(AdReportListResource::collection($reports))
            ->withPagination()
            ->send();
    }

    /**
     * GET /admin/ad-reports/{id}
     *
     * Full detail of a single report.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var AdminAdReportService $service */
        $service = $this->service;

        try {
            $report = $service->showReport($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('البلاغ غير موجود.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(new AdReportDetailResource($report))->send();
    }

    /**
     * POST /admin/ad-reports/{id}/reply
     *
     * Reply to a report. Sends email to the user.
     */
    public function reply(ReplyAdReportRequest $request, int $id): JsonResponse
    {
        /** @var AdminAdReportService $service */
        $service = $this->service;

        try {
            $report = $service->reply($id, $request->validated('reply'));
        } catch (ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('البلاغ غير موجود.'),
                httpStatus: 404,
            )->send();
        }

        return ApiResponse::respondWithModel(
            new AdReportDetailResource($report),
            message: __('تم إرسال الرد بنجاح.'),
        )->send();
    }
}
