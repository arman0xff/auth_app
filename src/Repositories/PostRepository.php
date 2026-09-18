<?php

namespace Repositories;

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

    public function createPost(int $userId, string $title, string $text): int {
        $sql = "INSERT INTO `posts` (user_id, title, text) VALUES (:userId, :title, :text)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["userId" => $userId, "title" => $title, "text" => $text]);

        return $this->pdo->lastInsertId();
    }

    public function updatePostData(int $postId, string $header, string $text): int {
        $sql = "UPDATE `posts` SET `title` = :header, `text` = :text WHERE `id` = :postId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["header" => $header, "text" => $text, "postId" => $postId]);

        return $sth->rowCount();
    }

    public function deletePostById(int $postId): int {
        $sql = "DELETE FROM `posts` WHERE `id` = :postId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId]);

        return $sth->rowCount();
    }
}