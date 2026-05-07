<?php

namespace App\Services;

use App\Enums\NafathVerificationStatus;
use App\Events\NafathVerificationFailed;
use App\Events\NafathVerificationSuccess;
use App\Models\NafathVerificationRequest;
use App\Repositories\Contracts\NafathVerificationRequestRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Str;

class NafathService
{
    /**
     * How long (in minutes) a verification request stays valid.
     */
    private const EXPIRY_MINUTES = 10;

    public function __construct(
        private readonly NafathVerificationRequestRepositoryContract $nafathRepository,
        private readonly UserRepositoryContract $userRepository,
    ) {}

    // ─── Public API ───────────────────────────────────────────────────────

    /**
     * Initiate a Nafath identity verification for the given user.
     *
     * Expires any previous pending requests, calls the (mock) Nafath API,
     * persists the verification request, and returns it.
     *
     * @param int    $userId
     * @param string $identityNumber
     * @return NafathVerificationRequest
     */
    public function initiateVerification(int $userId, string $identityNumber): NafathVerificationRequest
    {
        // Expire any outstanding pending requests for this user
        $this->nafathRepository->expirePendingForUser($userId);

        // Update the user's identity number
        $this->userRepository->update($userId, ['identity_number' => $identityNumber]);

        // Call the mock Nafath API to obtain a trans_id and random_code
        $nafathResponse = $this->callMockNafathApi($identityNumber);

        // Persist the verification request
        return $this->nafathRepository->create([
            'user_id'     => $userId,
            'trans_id'    => $nafathResponse['trans_id'],
            'random_code' => $nafathResponse['random_code'],
            'status'      => NafathVerificationStatus::PENDING->value,
            'expires_at'  => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);
    }

    /**
     * Handle the webhook callback from Nafath.
     *
     * Finds the request by trans_id, updates its status, and if approved
     * marks the user as verified. Broadcasts the appropriate Pusher event.
     *
     * @param string $transId
     * @param string $status  One of: approved, rejected, expired
     * @return NafathVerificationRequest
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \InvalidArgumentException
     */
    public function handleCallback(string $transId, string $status): NafathVerificationRequest
    {
        $verificationRequest = $this->nafathRepository->findByTransId($transId);

        if ($verificationRequest === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Nafath verification request not found for trans_id: {$transId}"
            );
        }

        // Validate the incoming status
        $resolvedStatus = NafathVerificationStatus::tryFrom($status);

        if ($resolvedStatus === null || $resolvedStatus === NafathVerificationStatus::PENDING) {
            throw new \InvalidArgumentException("Invalid Nafath callback status: {$status}");
        }

        // Only process if still pending (idempotency guard)
        if (! $verificationRequest->isPending()) {
            return $verificationRequest;
        }

        // Check if the request has expired by time even if status is still pending
        if ($verificationRequest->isExpired()) {
            $resolvedStatus = NafathVerificationStatus::EXPIRED;
        }

        // Persist the new status
        /** @var NafathVerificationRequest $updated */
        $updated = $this->nafathRepository->updateStatus(
            $verificationRequest->id,
            $resolvedStatus->value,
        );

        // If approved, mark the user as verified
        if ($resolvedStatus === NafathVerificationStatus::APPROVED) {
            $this->userRepository->update($updated->user_id, ['verified_by_nafath' => true]);
            broadcast(new NafathVerificationSuccess($updated));
        } else {
            broadcast(new NafathVerificationFailed($updated));
        }

        return $updated;
    }

    // ─── Mock Nafath API ──────────────────────────────────────────────────

    /**
     * Mock implementation of the Nafath API call.
     *
     * In production this would make an HTTP request to the real Nafath service.
     * Here it generates a unique trans_id and a 6-digit random_code to simulate
     * the response that Nafath would return.
     *
     * @param string $identityNumber
     * @return array{trans_id: string, random_code: string}
     */
    private function callMockNafathApi(string $identityNumber): array
    {
        // Simulate network latency (remove in production)
        // usleep(200_000);

        return [
            'trans_id'    => (string) Str::uuid(),
            'random_code' => (string) random_int(100000, 999999),
        ];
    }
}
