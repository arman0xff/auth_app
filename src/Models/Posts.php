<?php

namespace Models;

class Post {
    public function __construct (
        public int $id, public int $userId, public string $title, public string $content, public string $createdAt
    ) {}
}