<?php

namespace Services;

use E_POSTS_STATUSES;
use Exception;
use Interfaces\ICategoryRepository;
use Result;

readonly class CategoryService {
    public function __construct(private ICategoryRepository $categoryRepo) {
    }

    public function addNewCategory(string $name): int {
        if(empty($name) || strlen($name) < 2 || strlen($name) > 15) {
            throw new Exception("Text length is not correct");    
        }

        return $this->categoryRepo->createCategory($name);
    }

    public function getAllCategories(): ?array {
        return $this->categoryRepo->getAllCategories();
    }
}