<?php

namespace Repositories;

use PDO;
use Interfaces\ICategoryRepository;
use Exception;
use Override;

readonly class CategoryRepository implements ICategoryRepository {
    public function __construct(private PDO $pdo) {

    }

    public function createCategory(string $name): int {
        $sql = "INSERT INTO `categories` (name) VALUES (:name)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["name" => $name]);

        return $this->pdo->lastInsertId();
    }

    public function getAllCategories(): array {
        $sql = "SELECT * FROM `categories`";
        $sth = $this->pdo->query($sql);

        return $sth->fetchAll();
    }

    public function categoryExistsById(int $id): bool {
        $sql = "SELECT NULL FROM `categories` WHERE `id` = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["id" => $id]);

        return $sth->fetch() !== false;
    }

    public function editCategory(int $id, string $name): int {
        $sql = "UPDATE `categories` SET `name` = :name WHERE `id` = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["name" => $name, "id" => $id]);

        return $sth->rowCount();
    }

    public function deleteCategory(int $id): int {
        $sql = "DELETE FROM `categories` WHERE `id` = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["id" => $id]);

        return $sth->rowCount();
    }
}