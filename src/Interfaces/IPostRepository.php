<?php

namespace Interfaces;

interface IPostRepository {
    public function findUserPostsByUserId(int $userId);
    public function createPost(int $userId, string $title, string $text);
    public function updatePostData(int $postId, string $header, string $text);
    public function deletePostById(int $postId);
}