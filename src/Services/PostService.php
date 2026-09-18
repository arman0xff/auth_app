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
use Helpers;

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

    public function updatePost(int $postId, string $header, string $text): void {
        if(empty($header) || strlen($header) < 3 || strlen($header) > 64) {
            throw new Exception("Header length does not correct");    
        }
        if(empty($text) || strlen($text) < 10 || strlen($text) > 300) {
            throw new Exception("Main text length does not correct");    
        }

        $rowCount = $this->postRepo->updatePostData($postId, $header, $text);

        if($rowCount == 0) {
            throw new Exception("Error while updating post " . $postId);    
        }
    }

    public function deletePost(int $postId): void {
        $rowCount = $this->postRepo->deletePostById($postId);

        if($rowCount == 0) {
            throw new Exception("Error while deleting post " . $postId);    
        }
    }
}