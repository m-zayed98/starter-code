<?php

namespace App\Http\Controllers\Api\User;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\SubscribeToPackageRequest;
use App\Http\Resources\User\SubscriptionResource;
use App\Http\Resources\User\TransactionResource;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * GET /subscriptions/active
     *
     * Return the authenticated user's current active subscription.
     * The `adPackage` relation is eager-loaded by the repository.
     */
    public function active(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user         = auth('api')->user();
        $subscription = $this->subscriptionService->getActiveSubscription($user->id);

        if ($subscription === null) {
            return ApiResponse::respondWithArray(
                data: [],
                message: __('No active subscription found.'),
            )->send();
        }

        return ApiResponse::respondWithModel(
            new SubscriptionResource($subscription),
        )->send();
    }

    /**
     * POST /subscriptions
     *
     * Initiate a subscription to a package.
     * Creates a pending Transaction (with `transactionable` already loaded)
     * and returns it so the client can proceed with the payment flow.
     */
    public function subscribe(SubscribeToPackageRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user        = auth('api')->user();
        $transaction = $this->subscriptionService->initiateSubscription(
            $user->id,
            $request->validated('package_id'),
        );

        return ApiResponse::respondWithModel(
            new TransactionResource($transaction),
            message: __('Subscription initiated. Please complete the payment.'),
            httpStatus: 201,
        )->send();
    }
}
