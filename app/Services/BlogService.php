<?php

namespace App\Services;

use App\Repositories\Contracts\BlogRepositoryContract;
use App\Repositories\Contracts\CommentRepositoryContract;

/** @property BlogRepositoryContract $repository */
class BlogService extends BaseModelService
{
    protected CommentRepositoryContract $commentRepository;

    public function __construct(
        BlogRepositoryContract $repository,
        CommentRepositoryContract $commentRepository
    ) {
        parent::__construct($repository);
        $this->commentRepository = $commentRepository;
    }

    /**
     * Toggle blog active status.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function toggleStatus(int $id): \Illuminate\Database\Eloquent\Model
    {
        $blog = $this->showOrFail($id);
        
        $this->repository->update($id, [
            'is_active' => !$blog->is_active,
        ]);
        
        return $this->showOrFail($id);
    }

    /**
     * Delete blog with cascading comment deletion.
     *
     * @param int $id
     * @return bool
     */
    public function deleteBlogWithComments(int $id): bool
    {
        // Retrieve blog using showOrFail to ensure it exists
        $this->showOrFail($id);
        
        // Delete all comments associated with the blog
        $this->commentRepository->deleteByBlogId($id);
        
        // Delete blog with associated media
        return $this->repository->deleteWithMedia($id);
    }

    /**
     * Get blog with all comments.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getBlogWithComments(int $id): \Illuminate\Database\Eloquent\Model
    {
        return $this->repository->getBlogWithComments($id);
    }

    /**
     * Add comment to blog.
     *
     * @param int $blogId
     * @param int $userId
     * @param string $content
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function addComment(int $blogId, int $userId, string $content): \Illuminate\Database\Eloquent\Model
    {
        // Verify blog exists before adding comment
        $this->showOrFail($blogId);
        
        return $this->commentRepository->createComment($blogId, $userId, $content);
    }

    /**
     * Delete specific comment.
     *
     * @param int $commentId
     * @return bool
     */
    public function deleteComment(int $commentId): bool
    {
        return $this->commentRepository->delete($commentId);
    }

    /**
     * Toggle comment visibility status.
     *
     * @param int $commentId
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function toggleCommentVisibility(int $commentId): \Illuminate\Database\Eloquent\Model
    {
        return $this->commentRepository->toggleVisibility($commentId);
    }

    /**
     * Get active blogs for landing page.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getActiveBlogs(int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repository->getActiveBlogs($perPage);
    }
}
