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
        if(!$result) {
            return Result::fail("Error while executing query");
        }

        return Result::success("Post likes fetched successfully", $result);
    }

    // public function getPostsLikes(array $postIds): array {
    //     if(empty($postIds)) {
    //         return [];
    //     }

    //     return $this->likeRepo->getPostsLikes($postIds);
    // }
}