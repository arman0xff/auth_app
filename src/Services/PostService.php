<?php

namespace Services;

use DTOs\User\RegisterUserDto;
use DTOs\User\LoginUserDto;
use DTOs\User\DashboardUserDto;
use DTOs\User\ProfileUserDto;
use Exception;
use Mailer\Mailer;
use Models\User;
use E_SEND_MAIL_RETURN_CODES;
use Interfaces\IPostRepository;

readonly class PostService {
    public function __construct(private IPostRepository $postRepo) {
    }

    public function addNewPost(int $userId, string $header, string $text): int {
        if(empty($header) || strlen($header) < 3 || strlen($header) > 64) {
            throw new Exception("Header length does not correct");    
        }
        if(empty($text) || strlen($text) < 10 || strlen($text) > 300) {
            throw new Exception("Main text length does not correct");    
        }

        return $this->postRepo->createPost($userId, $header, $text);
    }

    public function getUserPostsByUserId(int $userId): array {
        return $this->postRepo->findUserPostsByUserId($userId);
    }

    public function updatePost(int $userId, int $postId, string $header, string $text): void {
        if(empty($header) || strlen($header) < 3 || strlen($header) > 64) {
            throw new Exception("Header length does not correct");    
        }
        if(empty($text) || strlen($text) < 10 || strlen($text) > 300) {
            throw new Exception("Main text length does not correct");    
        }

        $post = $this->postRepo->findPostById($postId);
        if ($post == null) {
            throw new Exception("Post not found");
        }

        if ($post['user_id'] != $userId) {
            throw new Exception("You don't have permission to edit this post");
        }

        $rowCount = $this->postRepo->updatePostData($postId, $header, $text);

        if($rowCount == 0) {
            throw new Exception("Error while updating post " . $postId);    
        }
    }

    public function deletePost(int $userId, int $postId): void {
        $post = $this->postRepo->findPostById($postId);
        if ($post == null) {
            throw new Exception("Post not found");
        }

        if ($post['user_id'] != $userId) {
            throw new Exception("You don't have permission to edit this post");
        }

        $rowCount = $this->postRepo->deletePostById($postId);

        if($rowCount == 0) {
            throw new Exception("Error while deleting post " . $postId);    
        }
    }

    public function getAllPosts(): array {
        $res = $this->postRepo->getAllPosts();

        if(sizeof($res) == 0) {
            throw new Exception("No post found");
        }

        return $res;
    }
}