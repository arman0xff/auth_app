<?php

namespace Controllers;

use Services\UserService;
use Services\PostService;
use Services\CategoryService;
use Services\ImagesService;
use Services\CommentService;
use Services\LikeService;
use Services\E_IMAGES_TYPES;
use Exception;

readonly class PostController {
    public function __construct(private PostService $postService, private UserService $userService, private CategoryService $catService,
        private CommentService $commentService, private LikeService $likeService) {
    }

    public function addNewPost(): void {
        $result = $this->postService->addNewPost($_SESSION["id"], $_POST["title"], $_POST["maintext"], $_POST["category"], $_POST["tags"], $_FILES['images'] ?? null);

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
            if(isset($userDto->profileImageName)) {
                $profileImageUrl = ImagesService::getWebDirByType(E_IMAGES_TYPES::Profile) . $userDto->profileImageName;
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
            $search = $_GET['search'] ?? null;
            $tags = $_GET['tags'] ?? null;
            $sort = $_GET['sort'] ?? null;
            $page = $_GET['page'] ?? null;

            $filters = [];

            if(!empty($search)) {
                $filters['search'] = $search;
            }

            if(!empty($categoryId) && $categoryId > 0) {
                $filters['categoryId'] = (int)$categoryId;
            }

            if(!empty($tags)) {
                $filters['tags'] = $tags;
            }

            if(!empty($sort)) {
                $filters['sort'] = $sort;
            }

            if(!empty($page)) {
                $filters['page'] = (int)$page;
            }
            else {
                $filters['page'] = 1;
            }

            $result = $this->postService->getAllPosts($filters);
            $posts = $result->value;
            $postIds = array_column($posts, 'id');

            $categories = $this->catService->getAllCategories();
            $allComments = $this->commentService->getPostsComments($postIds);
            $commentsByPost = [];

            foreach($allComments as $c) {
                $commentsByPost[$c['post_id']][] = $c;
            }

            $likedPosts = $this->likeService->getUserPostsWithLikes($_SESSION["id"], $postIds);
            $postsLikesResult = $this->likeService->getPostsLikes($postIds);

            foreach($posts as $key => $post) {
                $posts[$key]['comment'] = $commentsByPost[$post['id']] ?? [];
                
                if($postsLikesResult->isValid && array_key_exists($post['id'], $postsLikesResult->value)) {
                    $posts[$key]['likesCount'] = $postsLikesResult->value[$post['id']];
                }
                else {
                    $posts[$key]['likesCount'] = 0;
                }

                $posts[$key]['isLiked'] = in_array($post['id'], $likedPosts);
            }

            require_once __DIR__ . '/../Views/Posts.php';
        }
        catch(Exception $e) {
            echo("throw excep" . $e->getMessage());
        }
    }
}