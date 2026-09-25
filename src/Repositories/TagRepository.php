<?php

namespace Repositories;

use PDO;
use Interfaces\ITagRepository;
use Exception;
use Override;

readonly class TagRepository implements ITagRepository {
    public function __construct(private PDO $pdo) {

    }

    public function createTag(string $name): int {
        $sql = "INSERT INTO `tags` (name) VALUES (:name)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["name" => $name]);

        return $this->pdo->lastInsertId();
    }

    public function createMultipleTags(array $names): int {
        $sql = "INSERT IGNORE INTO `tags` (name) VALUES (";

        $isFirstDone = false;

        foreach($names as $name) {
            if(!$isFirstDone) {
                $sql = $sql . "?)";
                $isFirstDone = true;
            }
            else {
                $sql = $sql . ",(?)";
            }
        }

        $sth = $this->pdo->prepare($sql);

        $sth->execute($names);

        return $sth->rowCount();
    }

    public function createMultiplePostsTags(int $postId, array $names): int {
        $sql = "INSERT IGNORE INTO `post_tags` (post_id, tag_id) VALUES (";

        $isFirstDone = false;

        foreach($names as $name) {
            if(!$isFirstDone) {
                $sql = $sql . "?,?)";
                $isFirstDone = true;
            }
            else {
                $sql = $sql . ",(?,?)";
            }
        }

        $sth = $this->pdo->prepare($sql);

        $preparedArr = [];

        foreach($names as $name) {
            $preparedArr[] = $postId;
            $preparedArr[] = $name;
        }

        $sth->execute($preparedArr);

        return $sth->rowCount();
    }

    public function getAllTags(): array {
        $sql = "SELECT * FROM `tags`";
        $sth = $this->pdo->query($sql);

        return $sth->fetchAll();
    }

    public function getTagsIdsWithName(array $names): array {
        $sql = "SELECT `id` from `tags` WHERE `name` IN (";

        $isFirstDone = false;

        foreach($names as $name) {
            if(!$isFirstDone) {
                $sql = $sql . "?";
                $isFirstDone = true;
            }
            else {
                $sql = $sql . ",?";
            }
        }

        $sql = $sql . ")";

        $sth = $this->pdo->prepare($sql);

        $sth->execute($names);

        return $sth->fetchAll(PDO::FETCH_COLUMN);
    }

    public function tagExistsById(int $id): bool {
        $sql = "SELECT NULL FROM `tags` WHERE `id` = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["id" => $id]);

        return $sth->fetch() !== false;
    }

    public function editTag(int $id, string $name): int {
        $sql = "UPDATE `tags` SET `name` = :name WHERE `id` = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["name" => $name, "id" => $id]);

        return $sth->rowCount();
    }

    public function deleteTag(int $id): int {
        $sql = "DELETE FROM `tags` WHERE `id` = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["id" => $id]);

        return $sth->rowCount();
    }
}