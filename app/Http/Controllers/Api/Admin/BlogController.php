<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Facades\MediaUpload;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Http\Resources\Admin\BlogResource;
use App\Http\Resources\Admin\CommentResource;
use App\Services\BlogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends BaseApiController
{
    protected string $modelName     = 'Blog';
    protected string $serviceName   = BlogService::class;
    protected string $resource      = BlogResource::class;
    protected string $storeRequest  = StoreBlogRequest::class;
    protected string $updateRequest = UpdateBlogRequest::class;

    protected bool $usePermissions = true;

    protected array $queryOptions = [
        'index' => ['perPage' => 15, 'applyFilters' => true],
        'show'  => ['relations' => ['comments.user', 'media']],
    ];

    /**
     * Store a newly created blog in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->resolveFormRequest($this->storeRequest)->validated();

        $blog = $this->service->create($data);

        // Upload main images via MediaUpload facade
        if ($request->hasFile('main_image_ar')) {
            MediaUpload::file($request->file('main_image_ar'))
                ->collection('main_image_ar')
                ->uploadTo($blog);
        }

        if ($request->hasFile('main_image_en')) {
            MediaUpload::file($request->file('main_image_en'))
                ->collection('main_image_en')
                ->uploadTo($blog);
        }

        return ApiResponse::respondWithModel(
            new BlogResource($blog->refresh()),
            message: 'Blog created successfully.',
            httpStatus: 201,
        )->send();
    }

    /**
     * Update the specified blog in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $this->resolveFormRequest($this->updateRequest)->validated();

        $blog = $this->service->update($id, $data);

        // Upload new images if provided, replacing existing ones
        if ($request->hasFile('main_image_ar')) {
            $blog->clearMediaCollection('main_image_ar');
            MediaUpload::file($request->file('main_image_ar'))
                ->collection('main_image_ar')
                ->uploadTo($blog);
        }

        if ($request->hasFile('main_image_en')) {
            $blog->clearMediaCollection('main_image_en');
            MediaUpload::file($request->file('main_image_en'))
                ->collection('main_image_en')
                ->uploadTo($blog);
        }

        return ApiResponse::respondWithModel(
            new BlogResource($blog->refresh()),
            message: 'Blog updated successfully.',
        )->send();
    }

    /**
     * Remove the specified blog from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var BlogService $service */
        $service = $this->service;
        $service->deleteBlogWithComments($id);

        return ApiResponse::respondWithSuccess(
            message: 'Blog deleted successfully.',
        )->send();
    }

    /**
     * Toggle blog active status.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        /** @var BlogService $service */
        $service = $this->service;
        $blog = $service->toggleStatus($id);

        return ApiResponse::respondWithModel(
            new BlogResource($blog),
            message: 'Blog status updated successfully.',
        )->send();
    }

    /**
     * Toggle comment visibility status.
     */
    public function toggleCommentVisibility(int $commentId): JsonResponse
    {
        /** @var BlogService $service */
        $service = $this->service;
        $comment = $service->toggleCommentVisibility($commentId);

        return ApiResponse::respondWithModel(
            new CommentResource($comment),
            message: 'Comment visibility updated successfully.',
        )->send();
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroyComment(int $commentId): JsonResponse
    {
        /** @var BlogService $service */
        $service = $this->service;
        $service->deleteComment($commentId);

        return ApiResponse::respondWithSuccess(
            message: 'Comment deleted successfully.',
        )->send();
    }

    /**
     * Resolve and validate a FormRequest class, or fall back to the
     * current request if no class is specified.
     */
    private function resolveFormRequest(string $requestClass): Request
    {
        if ($requestClass === '') {
            return request();
        }

        return app($requestClass);
    }
}
