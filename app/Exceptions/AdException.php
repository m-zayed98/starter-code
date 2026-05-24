<?php

namespace App\Exceptions;

use App\Facades\ApiResponse;
use App\Services\ApiResponse\StatusCode;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Domain exception for ad-creation business rule violations.
 *
 * Usage:
 *   throw AdException::noActiveSubscription();
 *   throw AdException::quotaExceeded();
 */
class AdException extends Exception
{
    private string $statusCode;
    private int    $httpStatus;

    public function __construct(string $message, string $statusCode, int $httpStatus = 422)
    {
        parent::__construct($message);

        $this->statusCode = $statusCode;
        $this->httpStatus = $httpStatus;
    }

    // ─── Named constructors ───────────────────────────────────────────────

    /**
     * User tried to create an ad without an active subscription,
     * and ENABLE_AD_CREATION_WITHOUT_PACKAGE is disabled.
     */
    public static function noActiveSubscription(): self
    {
        return new self(
            message: __('You must have an active subscription to create an ad.'),
            statusCode: StatusCode::SUBSCRIPTION_EXPIRED,
            httpStatus: 422,
        );
    }

    /**
     * User has used all ad slots in their current subscription,
     * and ALLOW_ADDING_INFINITE_ADS is disabled.
     */
    public static function quotaExceeded(): self
    {
        return new self(
            message: __('You have reached the maximum number of ads allowed by your subscription.'),
            statusCode: StatusCode::SUBSCRIPTION_QUOTA_EXCEEDED,
            httpStatus: 422,
        );
    }

    // ─── Response builder ─────────────────────────────────────────────────

    public function render(): JsonResponse
    {
        return ApiResponse::respondWithError(
            message: $this->getMessage(),
            statusCode: $this->statusCode,
            httpStatus: $this->httpStatus,
        )->send();
    }

    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
