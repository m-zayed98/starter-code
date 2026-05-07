<?php

namespace App\Exceptions;

use App\Facades\ApiResponse;
use App\Services\ApiResponse\StatusCode;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Domain exception for subscription-related business rule violations.
 *
 * Usage:
 *   throw SubscriptionException::alreadySubscribed();
 *   throw SubscriptionException::packageNotAvailable();
 */
class SubscriptionException extends Exception
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

    public static function alreadySubscribed(): self
    {
        return new self(
            message:    __('You already have an active subscription. Please wait until it expires or cancel it first.'),
            statusCode: StatusCode::ALREADY_SUBSCRIBED,
            httpStatus: 422,
        );
    }

    public static function packageNotAvailable(): self
    {
        return new self(
            message:    __('This package is not available for subscription.'),
            statusCode: StatusCode::PACKAGE_NOT_AVAILABLE,
            httpStatus: 422,
        );
    }

    public static function offerNotStartedYet(): self
    {
        return new self(
            message:    __('Subscription to this offer has not started yet.'),
            statusCode: StatusCode::PACKAGE_NOT_AVAILABLE,
            httpStatus: 422,
        );
    }

    public static function subscriptionExpired(): self
    {
        return new self(
            message:    __('Your subscription has expired.'),
            statusCode: StatusCode::SUBSCRIPTION_EXPIRED,
            httpStatus: 422,
        );
    }

    public static function subscriptionCancelled(): self
    {
        return new self(
            message:    __('Your subscription has been cancelled.'),
            statusCode: StatusCode::SUBSCRIPTION_CANCELLED,
            httpStatus: 422,
        );
    }

    public static function quotaExceeded(): self
    {
        return new self(
            message:    __('You have exceeded your subscription ad quota.'),
            statusCode: StatusCode::SUBSCRIPTION_QUOTA_EXCEEDED,
            httpStatus: 422,
        );
    }

    // ─── Response builder ─────────────────────────────────────────────────

    public function render(): JsonResponse
    {
        return ApiResponse::respondWithError(
            message:    $this->getMessage(),
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
