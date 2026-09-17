<?php

namespace Interfaces;

interface IPostRepository {
    public function findUserPostsByUserId(int $userId);
}