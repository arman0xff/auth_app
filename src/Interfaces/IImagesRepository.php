<?php

namespace Interfaces;

interface IImagesRepository {
    public function createMultiplePostImages(int $postId, array $names);
    public function findPostImages(int $postId);
}