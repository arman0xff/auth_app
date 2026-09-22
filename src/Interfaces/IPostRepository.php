<?php

namespace Interfaces;

interface IPostRepository {
    public function findUserPostsByUserId(int $userId);
    public function findUserPostsWithStatusByUserId(int $userId, \E_POSTS_STATUSES $status);
    public function createPost(int $userId, string $title, string $text);
    public function updatePostData(int $postId, string $title, string $text, \E_POSTS_STATUSES $status);
    public function deletePostById(int $postId);
    public function getAllPosts();
    public function findPostById(int $postId);
}