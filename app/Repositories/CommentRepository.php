<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CommentRepository extends BaseRepository implements CommentRepositoryContract
{
    protected function resolveModel(): Model
    {
        return new Comment();
    }

    /**
     * Get all comments for a specific blog.
     *
     * @param int $blogId
     * @return Collection
     */
    public function getCommentsByBlogId(int $blogId): Collection
    {
        return $this->newQuery()
            ->where('blog_id', $blogId)
            ->newestFirst()
            ->with(['user'])
            ->get();
    }

    /**
     * Delete all comments for a specific blog.
     *
     * @param int $blogId
     * @return int
     */
    public function deleteByBlogId(int $blogId): int
    {
        return $this->newQuery()
            ->where('blog_id', $blogId)
            ->delete();
    }

    /**
     * Create comment with admin/user identification.
     *
     * @param int $blogId
     * @param int $userId
     * @param string $content
     * @return Model
     */
    public function createComment(int $blogId, int $userId, string $content): Model
    {
        return $this->create([
            'blog_id' => $blogId,
            'user_id' => $userId,
            'content' => $content,
            'is_visible' => true,
        ]);
    }

    /**
     * Toggle comment visibility status.
     *
     * @param int $commentId
     * @return Model
     */
    public function toggleVisibility(int $commentId): Model
    {
        $comment = $this->showOrFail($commentId);
        
        return $this->update($commentId, [
            'is_visible' => !$comment->is_visible,
        ]);
    }
}
