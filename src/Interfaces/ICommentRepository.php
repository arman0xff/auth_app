<?php

namespace Interfaces;

interface ICommentRepository {
    public function createComment(int $postId, int $userId, string $text, ?int $parentId = null);
    public function getCommentsByPostId(int $postId);
    public function getPostsComments(array $postIds);
    public function softDeleteComment(int $commentId);
}