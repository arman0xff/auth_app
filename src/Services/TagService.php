<?php

namespace Services;

use E_POSTS_STATUSES;
use Exception;
use Interfaces\ITagRepository;
use Result;

readonly class TagService {
    public function __construct(private ITagRepository $tagRepo) {
    }

    public function addMultipleTags(array $tags): Result {
        foreach($tags as &$tag) {
            if(strlen($tag) < 2 || strlen($tag) > 8) {
                Result::fail("Tag \"" . $tag . "\" have incorrect length");
            }

            $tag = strtolower($tag);
            $tag = trim($tag);
        }

        $this->tagRepo->createMultipleTags($tags);

        return Result::success("Tags created");
    }

    public function addMultiplePostsTags(int $postId, array $tags): int {
        return $this->tagRepo->createMultiplePostsTags($postId, $tags);
    }

    public function getTagsIdsWithName(array $tags): array {
        return $this->tagRepo->getTagsIdsWithName($tags);
    }

    public function getAllTags(): array {
        $res = $this->tagRepo->getAllTags();

        if(sizeof($res) == 0) {
            throw new Exception("No tag found");
        }

        return $res;
    }
}