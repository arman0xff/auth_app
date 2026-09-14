<?php

namespace Services;

use Interfaces\IAuthRepository;
use Exception;

class AuthService {
    public function __construct(private IAuthRepository $authRepo) {
    }

    public function createUserDefaultRole(int $userId): void {
        if(!$this->authRepo->createUserDefaultRole($userId)) {
            throw new Exception("Failed to create user default role");
        }
    }

    public function can(string $role, string $permission): bool {
        if(!isset($role)) {
            return false;
        }

        switch($permission) {
            case 'manage_users': {
                return $role === 'admin';
            }
            case 'access_moderator_page': {
                return $role === 'moderator' || $role === 'admin';
            }
            case 'access_admin_page': {
                return $role === 'admin';
            }
            case 'view_dashboard': {
                return $role === 'user' || $role === 'moderator' || $role === 'admin';
            }
            case 'view_users': {
                return $role === 'admin';
            }
            default: {
                return false;
            }
        }

        return false;
    }

    public function requireLogin(): bool {
        return !isset($_SESSION['id']);
    }

    public function requireRole(string $currRole, string $requiredRole): bool {
        return !isset($currRole) || $currRole !== $requiredRole;
    }

    public function hasRole(string $currRole, string $role): bool {
        return isset($currRole) && $currRole === $role;
    }

    public function changeUserRole(int $userId, string $newRole): bool {
        if(!$this->authRepo->isRoleExists($newRole)) {
            throw new Exception("Role doesn't exist");
        }

        return $this->authRepo->changeUserRole($userId, $newRole);
    }

    public function refreshUserRole(int $id): ?string {
        return $this->authRepo->getUserRoleById($id);
    }
}