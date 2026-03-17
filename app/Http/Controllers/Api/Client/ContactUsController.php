<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\ContactMessageStatus;
use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreContactUsRequest;
use App\Http\Resources\User\ContactMessageResource;
use App\Services\ContactMessageService;

class ContactUsController extends Controller
{
    public function __construct(
        private ContactMessageService $contactMessageService
    ) {}

    public function store(StoreContactUsRequest $request)
    {
        $contactMessage = $this->contactMessageService->create([
            ...$request->validated(),
            'status' => ContactMessageStatus::NOT_REPLITED,
        ]);

        return ApiResponse::respondWithModel(
            new ContactMessageResource($contactMessage),
            message: __('Created successfully'),
            httpStatus: 201
        )->send();
    }
}

