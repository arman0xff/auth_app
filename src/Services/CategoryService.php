<?php

namespace Services;

use E_POSTS_STATUSES;
use Exception;
use Interfaces\ICategoryRepository;
use Result;

readonly class CategoryService {
    public function __construct(private ICategoryRepository $categoryRepo) {
    }

    public function addNewCategory(string $name): Result {
        if(empty($name) || strlen($name) < 2 || strlen($name) > 15) {
            return Result::fail("Text length is not correct", 0);    
        }

        if(!$this->categoryRepo->createCategory($name)) {
            return Result::fail("Failed to create category", 1);
        }

        return Result::success("Category created");
    }

    public function editCategory(int $id, string $name): Result {
        if(!$this->categoryRepo->editCategory($id, $name)) {
            return Result::fail("Failed to edit category");
        }

        return Result::success("Category edited");
    }

    public function deleteCategory(int $id): Result {
        if(!$this->categoryRepo->deleteCategory($id)) {
            return Result::fail("Failed to delete category");
        }

        return Result::success("Category deleted");
    }

    public function getAllCategories(): ?array {
        return $this->categoryRepo->getAllCategories();
    }

    public function checkCategoryExistsById(int $id): bool {
        return $this->categoryRepo->categoryExistsById($id);
    }
}