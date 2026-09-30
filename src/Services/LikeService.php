<?php

namespace Services;

use Interfaces\ILikeRepository;
use Result;

readonly class LikeService {
    public function __construct(private ILikeRepository $likeRepo) {
    }

    public function addNewLike(int $postId, int $userId): Result {
        $likeId = $this->likeRepo->createOrDeleteLike($postId, $userId);

        if($likeId == 0) {
            return Result::fail("Failed to add like");
        }

        return Result::success("Like added successfully");
    }

    public function getLikesCountByPostId(int $postId): Result {
        $result = $this->likeRepo->getLikesCountByPostId($postId);
        if($result === false) {
            return Result::fail("Error while executing query");
        }

        return Result::success("Post likes fetched successfully", $result);
    }

    public function getPostsLikes(array $postIds): Result {
        if(empty($postIds)) {
            return Result::fail("Post ids is empty");
        }

        $result = $this->likeRepo->getPostsLikes($postIds);

        return Result::success("Post likes fetched successfully", $result);
    }

    public function getUserPostsWithLikes(int $userId, array $postIds): array {
        if(empty($postIds)) {
            return [];
        }

        return $this->likeRepo->getUserPostsWithLikes($userId, $postIds);
    }
}