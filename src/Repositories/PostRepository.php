<?php

namespace Repositories;

use E_POSTS_STATUSES;
use PDO;
use Interfaces\IPostRepository;
use Exception;
use E_SEND_MAIL_RETURN_CODES;
use Override;

readonly class PostRepository implements IPostRepository {
    public function __construct(private PDO $pdo) {

    }

    public function findUserPostsByUserId(int $userId): array {
        $sql = "SELECT * FROM `posts` WHERE `user_id` = :userId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["userId" => $userId]);

        return $sth->fetchAll();
    }

    public function findUserPostsWithStatusByUserId(int $userId, \E_POSTS_STATUSES $status): array {
        $sql = "SELECT * FROM `posts` WHERE `user_id` = :userId AND `status` = :status";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["userId" => $userId, "status" => $status->name]);

        return $sth->fetchAll();
    }

    public function createPost(int $userId, string $title, string $text, int $categoryId): int {
        $sql = "INSERT INTO `posts` (user_id, title, text, category_id) VALUES (:userId, :title, :text, :categoryId)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["userId" => $userId, "title" => $title, "text" => $text, "categoryId" => $categoryId]);

        return $this->pdo->lastInsertId();
    }

    public function updatePostData(int $postId, string $title, string $text, E_POSTS_STATUSES $status): int {
        $sql = "UPDATE `posts` SET `title` = :title, `text` = :text, `status` = :status WHERE `id` = :postId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["title" => $title, "text" => $text, "postId" => $postId, "status" => $status->value]);

        return $sth->rowCount();
    }

    public function findPostById(int $postId): ?array {
        $sql = "SELECT * FROM `posts` WHERE `id` = :postId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId]);
        $post = $sth->fetch();

        return $post ?: null;
    }

    public function deletePostById(int $postId): int {
        $sql = "DELETE FROM `posts` WHERE `id` = :postId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId]);

        return $sth->rowCount();
    }

    public function getAllPosts(): array {
        $sql = "SELECT p.`user_id`, p.`title`, p.`text`, p.`created_at`, u.`first_name`, u.`last_name`, u.`image_id`
            FROM `posts` p JOIN `users` u ON p.`user_id` = u.`id` WHERE p.`status` = 'published' ORDER BY `created_at` DESC";
        $sth = $this->pdo->query($sql);

        return $sth->fetchAll();
    }

    public function getAllPostsWithCategory(int $categoryId): array {
        $sql = "SELECT p.`user_id`, p.`title`, p.`text`, p.`created_at`, u.`first_name`, u.`last_name`, u.`image_id`
            FROM `posts` p JOIN `users` u ON p.`user_id` = u.`id` WHERE p.`status` = 'published' AND p.`category_id` = :categoryId ORDER BY `created_at` DESC";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["categoryId" => $categoryId]);

        return $sth->fetchAll();
    }
}