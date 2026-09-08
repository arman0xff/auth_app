<?php

namespace Interfaces;

interface IAuthRepository {
    public function createUserDefaultRole(int $userId);
    public function changeUserRole(int $userId, string $newRole);
    public function getUserRoleById(int $userId): ?string;
    public function isRoleExists(string $role): bool;
}