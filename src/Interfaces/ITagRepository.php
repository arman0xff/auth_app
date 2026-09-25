<?php

namespace Interfaces;

interface ITagRepository {
    public function createTag(string $name);
    public function createMultipleTags(array $names);
    public function createMultiplePostsTags(int $postId, array $names);
    public function getAllTags();
    public function getTagsIdsWithName(array $names);
    public function tagExistsById(int $id);
    public function editTag(int $id, string $name);
    public function deleteTag(int $id);
}