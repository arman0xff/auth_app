<?php

namespace Controllers;

use Services\UserService;
use Services\PostService;
use Services\CategoryService;
use Exception;

readonly class PostController {
    public function __construct(private PostService $postService, private UserService $userService, private CategoryService $catService) {
        
    }

    public function addNewPost(): void {
        $result = $this->postService->addNewPost($_SESSION["id"], $_POST["title"], $_POST["maintext"], $_POST["category"], $_POST["tags"]);

        if(!$result->isValid) {
            http_response_code(500);
            header('Location: ' . PROFILE_USER_ROUTE);
            exit;
        }
        
        http_response_code(200);
        header('Location: ' . PROFILE_USER_ROUTE);
        exit;
    }

    public function updatePost(): void {
        $userId = $_SESSION['id'];
        $postId = (int)$_POST['post_id'];
        
        $result = $this->postService->updatePost($userId, $postId, $_POST['title'], $_POST['text'], $_POST['status']);

        if(!$result->isValid) {
            $_SESSION['error'] = $result->message;
        }

        http_response_code(200);
        header('Location: ' . PROFILE_USER_ROUTE);
        exit;
    }

    public function showProfileWithEditablePost(): void {
        $userId = $_SESSION["id"];
        $postId = $_GET["post_id"] ?? null;

        $result = $this->userService->getUserData($userId);

        if(!$result->isValid) {
            http_response_code(400);
            exit;
        }

        $userDto = $result->value;

        if($userDto == null) {
            $error = "Requested profile ID not found";
        }
        else {
            if(isset($userDto->profileImageId)) {
                $profileImageUrl = $this->userService->getProfileImageUrl($userDto->profileImageId);
            }
            
            $userPosts = $this->postService->getUserPostsByUserId($userId);
        }

        $editingPostId = $postId;
        $categories = $this->catService->getAllCategories();

        require_once __DIR__ . '/../Views/Profile.php';
    }

    public function deletePost(): void {
        $userId = $_SESSION["id"];
        $postId = $_POST["post_id"] ?? null;

        $this->postService->deletePost($userId, $postId);

        header('Location: ' . PROFILE_USER_ROUTE);
        exit;
    }

    public function showAllPosts(): void {
        try {
            $categoryId = $_GET['category'] ?? null;

            if(empty($categoryId) || $categoryId == 0) {
                $posts = $this->postService->getAllPosts();
            }
            else {
                $posts = $this->postService->getAllPostsWithCategory($categoryId);
            }

            $categories = $this->catService->getAllCategories();

            require_once __DIR__ . '/../Views/Posts.php';
        }
        catch(Exception $e) {
            echo("throw excep" . $e->getMessage());
        }
    }
}