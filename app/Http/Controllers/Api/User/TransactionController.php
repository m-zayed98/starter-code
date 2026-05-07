<?php

namespace App\Http\Controllers\Api\User;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProcessTransactionRequest;
use App\Http\Resources\User\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * POST /transactions/{id}/process
     *
     * Mock payment gateway callback endpoint.
     * Accepts a terminal status (completed | failed | cancelled) and an
     * optional external reference, then processes the transaction accordingly.
     *
     * The `transactionable` relation is already loaded by the repository and
     * re-attached by TransactionService — no extra query needed here.
     */
    public function process(ProcessTransactionRequest $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $transaction = $this->transactionService->process(
            $id,
            $user->id,
            $request->validated(),
        );

        return ApiResponse::respondWithModel(
            new TransactionResource($transaction),
            message: __('Transaction processed successfully.'),
        )->send();
    }
}
