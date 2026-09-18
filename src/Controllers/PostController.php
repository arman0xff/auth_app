<?php

namespace Controllers;

use Services\UserService;
use Services\PostService;
use Exception;
use E_SEND_MAIL_RETURN_CODES;

readonly class PostController {
    public function __construct(private PostService $postService, private UserService $userService) {
        
    }

    public function addNewPost(): void {
        $lastInsertId = $this->postService->addNewPost($_SESSION["id"], $_POST["header"], $_POST["maintext"]);

        if($lastInsertId == 0) {
            http_response_code(500);
            header('Location: ' . PROFILE_USER_ROUTE);
            exit;
        }
        
        http_response_code(200);
        header('Location: ' . PROFILE_USER_ROUTE);
        exit;
    }

    public function updatePost(): void {
        $this->postService->updatePost($_POST['post_id'], $_POST['header'], $_POST['text']);
        
        http_response_code(200);
        header('Location: ' . PROFILE_USER_ROUTE);
        exit;
    }

    public function showProfileWithEditablePost(): void {
        $userId = $_SESSION["id"];
        $postId = $_GET["post_id"] ?? null;

        $userDto = $this->userService->getUserData($userId);

        if($userDto == null) {
            $error = "Requested profile ID not found";
        }
        else {
            $userDto->profileImageUrl = $this->userService->getProfileImageUrl($userId);
            $userPosts = $this->postService->getUserPostsByUserId($userId);
        }

        $editingPostId = $postId;

        require_once __DIR__ . '/../Views/Profile.php';
    }

    public function deletePost(): void {
        $userId = $_SESSION["id"];
        $postId = $_GET["post_id"] ?? null;

        $userDto = $this->userService->getUserData($userId);

        if($userDto == null) {
            $error = "Requested profile ID not found";
        }
        else {
            $userDto->profileImageUrl = $this->userService->getProfileImageUrl($userId);
            $this->postService->deletePost($_GET['post_id']);
            $userPosts = $this->postService->getUserPostsByUserId($userId);
        }

        require_once __DIR__ . '/../Views/Profile.php';
    }

    public function showAllPosts(): void {
        try {
            $posts = $this->postService->getAllPosts();
            require_once __DIR__ . '/../Views/Posts.php';
        }
        catch(Exception $e) {
            echo("throw excep" . $e->getMessage());
        }
    }
}