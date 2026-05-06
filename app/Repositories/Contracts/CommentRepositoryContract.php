<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface CommentRepositoryContract extends RepositoryContract
{
    /**
     * Get all comments for a specific blog.
     *
     * @param int $blogId
     * @return Collection
     */
    public function getCommentsByBlogId(int $blogId): Collection;

    /**
     * Delete all comments for a specific blog.
     *
     * @param int $blogId
     * @return int
     */
    public function deleteByBlogId(int $blogId): int;

    /**
     * Create comment with admin/user identification.
     *
     * @param int $blogId
     * @param int $userId
     * @param string $content
     * @return Model
     */
    public function createComment(int $blogId, int $userId, string $content): Model;

    /**
     * Toggle comment visibility status.
     *
     * @param int $commentId
     * @return Model
     */
    public function toggleVisibility(int $commentId): Model;
}
