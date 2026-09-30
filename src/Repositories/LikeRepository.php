<?php

namespace Repositories;

use PDO;
use Interfaces\ILikeRepository;

readonly class LikeRepository implements ILikeRepository {
    public function __construct(private PDO $pdo) {

    }

    public function createLike(int $postId, int $userId): int {
        $sql = "INSERT IGNORE INTO `post_likes` (post_id, user_id) VALUES (:postId, :userId)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId, "userId" => $userId]);

        return $sth->rowCount();
    }

    public function createOrDeleteLike(int $postId, int $userId): int {
        $sql = "INSERT IGNORE INTO `post_likes` (post_id, user_id) VALUES (:postId, :userId)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId, "userId" => $userId]);

        if($sth->rowCount() == 0) {
            return $this->deleteLike($postId, $userId);
        }

        return $sth->rowCount();
    }

    public function deleteLike(int $postId, int $userId): int {
        $sql = "DELETE FROM `post_likes` WHERE `post_id` = :postId AND `user_id` = :userId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId, "userId" => $userId]);

        return $sth->rowCount();
    }

    public function getLikesCountByPostId(int $postId): int|bool {
        $sql = "SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = :postId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId]);

        return $sth->fetchColumn();
    }

    public function getPostsLikes(array $postIds): array {
        $sql = "SELECT `post_id`, COUNT(*) as `count` FROM `post_likes` WHERE `post_id` IN(";

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

        $sql = $sql . ") GROUP BY `post_id`";
        $sth = $this->pdo->prepare($sql);

        $sth->execute($postIds);

        return $sth->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getUserPostsWithLikes(int $userId, array $postIds): array {
        $sql = "SELECT `post_id` FROM `post_likes` WHERE `post_id` IN(";

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

        $sql = $sql . ") AND `user_id` = ?";
        $sth = $this->pdo->prepare($sql);

        $postIds[] = $userId;

        $sth->execute($postIds);

        return $sth->fetchAll(PDO::FETCH_COLUMN);
    }
}