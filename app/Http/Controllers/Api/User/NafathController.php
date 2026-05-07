<?php

namespace App\Http\Controllers\Api\User;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\InitiateNafathVerificationRequest;
use App\Http\Requests\User\NafathCallbackRequest;
use App\Http\Resources\User\NafathVerificationRequestResource;
use App\Services\NafathService;
use Illuminate\Http\JsonResponse;

class NafathController extends Controller
{
    public function __construct(
        private readonly NafathService $nafathService,
    ) {}

    /**
     * POST /nafath/verify
     *
     * Initiate a Nafath identity verification for the authenticated user.
     * Returns the verification request containing the trans_id and random_code
     * that the user must confirm inside the Nafath app.
     *
     * The client should subscribe to the private Pusher channel
     * `private-nafath.{id}` (where `id` is the returned request id)
     * to receive real-time VerificationSuccess / VerificationFailed events.
     */
    public function initiate(InitiateNafathVerificationRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $verificationRequest = $this->nafathService->initiateVerification(
            $user->id,
            $request->validated('identity_number'),
        );

        return ApiResponse::respondWithModel(
            new NafathVerificationRequestResource($verificationRequest),
            message: __('Nafath verification initiated. Please confirm in the Nafath app.'),
            httpStatus: 201,
        )->send();
    }

    /**
     * POST /nafath/callback
     *
     * Webhook endpoint called by Nafath to deliver the verification result.
     * Updates the request status, marks the user as verified if approved,
     * and broadcasts the result over the private Pusher channel.
     *
     * This endpoint is intentionally unauthenticated so Nafath can reach it.
     */
    public function callback(NafathCallbackRequest $request): JsonResponse
    {
        try {
            $verificationRequest = $this->nafathService->handleCallback(
                $request->validated('trans_id'),
                $request->validated('status'),
            );

            return ApiResponse::respondWithModel(
                new NafathVerificationRequestResource($verificationRequest),
                message: __('Nafath callback processed successfully.'),
            )->send();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::respondWithError(
                __('Verification request not found.'),
                httpStatus: 404,
            )->send();
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::respondWithError(
                $e->getMessage(),
                httpStatus: 422,
            )->send();
        }
    }
}
