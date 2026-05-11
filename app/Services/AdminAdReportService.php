<?php

namespace App\Services;

use App\Mail\AdReportReplyMail;
use App\Models\AdReport;
use App\Repositories\Contracts\AdReportRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;

class AdminAdReportService extends BaseModelService
{
    public function __construct(AdReportRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Return a paginated list of all reports for the admin panel.
     */
    public function listReports(int $perPage = 15): LengthAwarePaginator
    {
        /** @var AdReportRepositoryContract $repository */
        $repository = $this->repository;

        return $repository->paginateForAdmin($perPage);
    }

    /**
     * Return a single report with full details for the admin detail view.
     *
     * @throws ModelNotFoundException
     */
    public function showReport(int $reportId): AdReport
    {
        /** @var AdReportRepositoryContract $repository */
        $repository = $this->repository;

        $report = $repository->findWithDetails($reportId);

        if ($report === null) {
            throw new ModelNotFoundException(
                "Report #{$reportId} not found."
            );
        }

        return $report;
    }

    /**
     * Reply to a report and send email to the user.
     *
     * @throws ModelNotFoundException
     */
    public function reply(int $reportId, string $replyText): AdReport
    {
        /** @var AdReportRepositoryContract $repository */
        $repository = $this->repository;

        $report = $repository->findWithDetails($reportId);

        if ($report === null) {
            throw new ModelNotFoundException(
                "Report #{$reportId} not found."
            );
        }

        $report = $repository->update($reportId, [
            'reply' => $replyText,
            'status' => 'replied',
            'replied_at' => now(),
        ]);

        // Send email to user
        if (! empty($report->user?->email)) {
            try {
                Mail::to($report->user->email)->send(new AdReportReplyMail($report));
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::error('Failed to send ad report reply email', [
                    'report_id' => $reportId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $repository->findWithDetails($reportId);
    }
}
