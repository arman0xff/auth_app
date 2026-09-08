<?php

namespace Interfaces;

interface IAuthRepository {
    public function createUserDefaultRole(int $userId);
    public function changeUserRole(int $userId, string $newRole);
}