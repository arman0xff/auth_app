<?php

namespace Repositories;

use PDO;
use Interfaces\ICategoryRepository;
use Exception;
use Override;

readonly class CategoryRepository implements ICategoryRepository {
    public function __construct(private PDO $pdo) {

    }

    public function getAllCategories(): array {
        $sql = "SELECT * FROM `categories`";
        $sth = $this->pdo->query($sql);

        return $sth->fetchAll();
    }
}