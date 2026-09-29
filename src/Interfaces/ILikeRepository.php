<?php

namespace Interfaces;

interface ILikeRepository {
    public function createLike(int $postId, int $userId);
    public function createOrDeleteLike(int $postId, int $userId);
    public function deleteLike(int $postId, int $userId);
    public function getLikesCountByPostId(int $postId);
}