<?php

namespace Services;

use Interfaces\ICommentRepository;
use Result;

readonly class CommentService {
    public function __construct(private ICommentRepository $commentRepo) {
    }

    public function addNewComment(int $postId, int $userId, string $text, ?int $parentId = null): Result {
        if(empty($text) || strlen($text) < 10 || strlen($text) > 255) {
            return Result::fail("Text length is not correct");    
        }

        $commentId = $this->commentRepo->createComment($postId, $userId, $text, $parentId);

        if($commentId == 0) {
            return Result::fail("Failed to create comment");
        }

        return Result::success("Comment created successfully");
    }

    public function getCommentsByPostId(int $postId): array {
        return $this->commentRepo->getCommentsByPostId($postId);
    }

    public function getPostsComments(array $postIds): array {
        if(empty($postIds)) {
            return [];
        }

        return $this->commentRepo->getPostsComments($postIds);
    }
}