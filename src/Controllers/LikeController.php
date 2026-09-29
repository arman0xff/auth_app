<?php

namespace Controllers;

use Services\LikeService;

readonly class LikeController {
    public function __construct(private LikeService $likeService) {      
    }

    public function addNewLike(): void {
        $postId = $_POST['post_id'] ?? 0;

        if($postId > 0) {
            $result = $this->likeService->addNewLike($postId, $_SESSION['id']);
            header('Content-type: text/plain');

            $result = $this->likeService->getLikesCountByPostId($postId);
           
            if($result->isValid) {
                echo($result->value);
            }
            exit;
        }
    }

    public function getPostLikesCount(): void {
        $postId = $_POST['post_id'] ?? 0;

        if($postId > 0) {
            $this->likeService->getLikesCountByPostId($postId);
        }
    }
}