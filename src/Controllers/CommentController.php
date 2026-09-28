<?php

namespace Controllers;

use Services\CommentService;

readonly class CommentController {
public function __construct(private CommentService $commentService) {
        
    }

    public function addNewComment(): void {
        $postId = $_POST['post_id'] ?? 0;
        $text = trim($_POST['text'] ?? '');

        if(!empty($text) && $postId > 0) {
            $this->commentService->addNewComment($postId, $_SESSION['id'], $text, !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null);
        }

        header('Location: ' . POSTS_ROUTE);
        exit;
    }
}