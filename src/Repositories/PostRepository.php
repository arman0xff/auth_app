<?php

namespace Repositories;

use E_POSTS_STATUSES;
use PDO;
use Interfaces\IPostRepository;

readonly class PostRepository implements IPostRepository {
    public function __construct(private PDO $pdo) {

    }

    public function findUserPostsByUserId(int $userId): array {
        $sql = "SELECT p.*, GROUP_CONCAT(DISTINCT t.`name`) AS `tags`, GROUP_CONCAT(DISTINCT pi.`file_name`) AS `images` 
            FROM `posts` p LEFT JOIN `post_tags` pt ON pt.`post_id` = p.`id` LEFT JOIN `tags` t ON t.`id` = pt.`tag_id` 
            LEFT JOIN `post_images` pi ON pi.`post_id` = p.`id` WHERE `user_id` = :userId GROUP BY p.`id` ORDER BY `created_at` DESC";
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

    public function getAllPosts(array $filters = []): array {
        $sql = "SELECT p.`id`, p.`user_id`, p.`title`, p.`text`, p.`created_at`, u.`first_name`, u.`last_name`, u.`profile_image_name`, 
            GROUP_CONCAT(DISTINCT t.`name`) AS `tags`, GROUP_CONCAT(DISTINCT pi.`file_name`) AS `images`,
            (SELECT COUNT(*) FROM `post_likes` pl WHERE pl.`post_id` = p.`id`) AS `likes_count`,
            (SELECT COUNT(*) FROM `post_comments` pc WHERE pc.`post_id` = p.`id`) AS `comments_count`
            FROM `posts` p JOIN `users` u ON p.`user_id` = u.`id` LEFT JOIN `post_tags` pt ON pt.`post_id` = p.`id` 
            LEFT JOIN `tags` t ON t.`id` = pt.`tag_id` LEFT JOIN `post_images` pi ON pi.`post_id` = p.`id` 
            WHERE p.`status` = 'published' AND (p.`title` LIKE ? OR p.`text` LIKE ?) 
            AND (? IS NULL OR p.`category_id` = ?) ";
    
        $searchArr = '%' . trim($filters['search'] ?? '') . '%';
        $categoryId = !empty($filters['categoryId']) ? $filters['categoryId'] : null;

        $params = [$searchArr, $searchArr, $categoryId, $categoryId];

        if(!empty($filters['tags'])) {
            $sql .= " AND t.`name` IN (";

            $tags = trim($filters['tags']);
            $tagsArr = array_values(explode(",", $tags));

            $isFirst = true;
            foreach($tagsArr as $tag) {
                $params[] = $tag;

                if($isFirst) {
                    $sql .= "?";
                    $isFirst = false;
                }
                else {
                    $sql .= ",?";
                }
            }

            $sql .= ") GROUP BY p.`id` ORDER BY ";
        }
        else {
            $sql .= " GROUP BY p.`id` ORDER BY ";
        }

        $sql .= match($filters['sort'] ?? null) {
            'most_liked' => '`likes_count` DESC, p.`created_at` DESC',
            'most_commented' => '`comments_count` DESC, p.`created_at` DESC',
            default => 'p.`created_at` DESC',
        };

        $page = $filters['page'] ?? null;
        if(!empty($page)) {
            $limit = $page * 10;
            $offset = ($page - 1) * 10;
            $sql .= " LIMIT " . $limit . " OFFSET " . $offset;
        }

        $sth = $this->pdo->prepare($sql);

        $sth->execute($params);

        return $sth->fetchAll();
    }

    public function getAllPostsWithCategory(int $categoryId): array {
        $sql = "SELECT p.`id`, p.`user_id`, p.`title`, p.`text`, p.`created_at`, u.`first_name`, u.`last_name`, u.`profile_image_name`, GROUP_CONCAT(t.`name`) AS `tags`, GROUP_CONCAT(DISTINCT pi.`file_name`) AS `images` 
            FROM `posts` p JOIN `users` u ON p.`user_id` = u.`id` LEFT JOIN `post_tags` pt ON pt.`post_id` = p.`id` LEFT JOIN `tags` t ON t.`id` = pt.`tag_id` LEFT JOIN `post_images` pi ON pi.`post_id` = p.`id` 
            WHERE p.`status` = 'published' AND p.`category_id` = :categoryId GROUP BY p.`id` ORDER BY `created_at` DESC";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["categoryId" => $categoryId]);

        return $sth->fetchAll();
    }
}