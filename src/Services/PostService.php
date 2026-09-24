<?php

namespace Services;

use E_POSTS_STATUSES;
use Exception;
use Interfaces\IPostRepository;
use Result;

readonly class PostService {
    public function __construct(private IPostRepository $postRepo, private AuthService $authService, private CategoryService $catService) {
    }

    public function addNewPost(int $userId, string $title, string $text, int $categoryId): int {
        if(empty($title) || strlen($title) < 3 || strlen($title) > 64) {
            throw new Exception("Title length is not correct");    
        }
        if(empty($text) || strlen($text) < 10 || strlen($text) > 300) {
            throw new Exception("Main text length is not correct");    
        }

        if(!$this->catService->checkCategoryExistsById($categoryId)) {
            throw new Exception("Selected category not found");
        }

        return $this->postRepo->createPost($userId, $title, $text, $categoryId);
    }

    public function getUserPostsByUserId(int $userId): array {
        $sUserId = $_SESSION['id'] ?? null;

        if(isset($sUserId)) {
            if($sUserId == $userId) {
                return $this->postRepo->findUserPostsByUserId($userId);
            }

            $role = $this->authService->refreshUserRole($sUserId);

            if($this->authService->can($role, "view_users")) {
                return $this->postRepo->findUserPostsByUserId($userId);
            }
        }

        return $this->postRepo->findUserPostsWithStatusByUserId($userId, E_POSTS_STATUSES::Published);
    }

    public function updatePost(int $userId, int $postId, string $title, string $text, string $status): Result {
        if(empty($title) || strlen($title) < 3 || strlen($title) > 64) {
            return Result::fail("Title length is not correct");    
        }
        if(empty($text) || strlen($text) < 10 || strlen($text) > 300) {
            return Result::fail("Main text length is not correct");    
        }

        $eStatus = E_POSTS_STATUSES::tryFrom($status);
        if(empty($eStatus)) {
            return Result::fail("Status don't recognised");    
        }

        $post = $this->postRepo->findPostById($postId);
        if($post == null) {
            return Result::fail("Post not found");
        }

        if($post['user_id'] != $userId) {
            return Result::fail("You don't have permission to edit this post");
        }

        if($post['title'] == $title && $post['text'] == $text && $post['status'] == $status) {
            return Result::fail("No data to edit");
        }

        $rowCount = $this->postRepo->updatePostData($postId, $title, $text, $eStatus);

        if($rowCount == 0) {
            return Result::fail("Error while updating post " . $postId);    
        }

        return Result::success("Post updated successfully");
    }

    public function deletePost(int $userId, int $postId): void {
        $post = $this->postRepo->findPostById($postId);
        if($post == null) {
            throw new Exception("Post not found");
        }

        if($post['user_id'] != $userId) {
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