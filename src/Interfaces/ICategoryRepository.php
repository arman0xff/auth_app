<?php

namespace Interfaces;

interface ICategoryRepository {
    public function createCategory(string $name);
    public function getAllCategories();
    public function categoryExistsById(int $id);
    public function editCategory(int $id, string $name);
    public function deleteCategory(int $id);
}