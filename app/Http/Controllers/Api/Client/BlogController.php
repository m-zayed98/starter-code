<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreCommentRequest;
use App\Http\Resources\Client\BlogResource;
use App\Http\Resources\Client\CommentResource;
use App\Services\BlogService;
use Illuminate\Http\JsonResponse;

class BlogController extends BaseApiController
{
    protected string $modelName     = 'Blog';
    protected string $serviceName   = BlogService::class;
    protected string $resource      = BlogResource::class;

    protected bool $usePermissions = false;

    protected array $queryOptions = [
        'index' => ['perPage' => 10, 'applyFilters' => true],
        'show'  => ['relations' => ['comments.user', 'media']],
    ];

    public function __construct()
    {
        parent::__construct();

        // Merge filter to only show active blogs for public API
        request()->merge(['status' => '1']); // is_active = true
    }

    /**
     * Store a new comment for the blog (authenticated user).
     */
    public function storeComment(StoreCommentRequest $request, int $id): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return ApiResponse::respondWithError(
                message: 'Unauthenticated.',
                httpStatus: 401
            )->send();
        }

        /** @var BlogService $service */
        $service = $this->service;
        $comment = $service->addComment(
            $id,
            $user->id,
            $request->validated('content')
        );

        return ApiResponse::respondWithModel(
            new CommentResource($comment),
            message: 'Comment created successfully.',
            httpStatus: 201,
        )->send();
    }
}
