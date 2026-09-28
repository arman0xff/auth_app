<?php

namespace Repositories;

use PDO;
use Interfaces\ICommentRepository;

readonly class CommentRepository implements ICommentRepository {
    public function __construct(private PDO $pdo) {

    }

    public function createComment(int $postId, int $userId, string $text, ?int $parentId = null): int {
        $sql = "INSERT INTO `post_comments` (post_id, user_id, parent_id, text) VALUES (:postId, :userId, :parentId, :text)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId, "userId" => $userId, "parentId" => $parentId, "text" => $text]);

        return $this->pdo->lastInsertId();
    }

    public function getCommentsByPostId(int $postId): array {
        $sql = "SELECT pc.*, u.`first_name`, u.`last_name`, u.`profile_image_name` FROM `post_comments` pc LEFT JOIN `users` u ON u.`id` = pc.`user_id`
            WHERE pc.`post_id` = :postId ORDER BY pc.`created_at` ASC";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId]);

        return $sth->fetchAll();
    }

    public function getPostsComments(array $postIds): array {
        $sql = "SELECT pc.*, u.`first_name`, u.`last_name`, u.`profile_image_name` FROM `post_comments` pc LEFT JOIN `users` u ON u.`id` = pc.`user_id`
            WHERE pc.`post_id` IN(";

        $isFirst = true;
        foreach($postIds as $postId) {
            if($isFirst) {
                $sql = $sql . "?";
                $isFirst = false;
            }
            else {
                $sql = $sql . ",?";
            }
        }

        $sql = $sql . ") ORDER BY pc.`created_at` ASC";
        $sth = $this->pdo->prepare($sql);

        $sth->execute($postIds);

        return $sth->fetchAll();
    }

    public function softDeleteComment(int $commentId): bool {
        $sql = "UPDATE `post_comments` SET `deleted_at` = NOW() WHERE `id` = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["id" => $commentId]);

        return $sth->rowCount() > 0;
    }
}