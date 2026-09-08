<?php

namespace Services;

use DTOs\User\RegisterUserDto;
use Interfaces\IAuthRepository;
use Exception;

class AuthService {
    public function __construct(private IAuthRepository $authRepo) {
    }

    public function createUserDefaultRole(int $userId): void {
        $this->authRepo->createUserDefaultRole($userId);
    }

    public function can(string $permission): bool {
        if(!isset($_SESSION['role'])) {
            return false;
        }

        switch($permission) {
            case 'manage_users': {
                return $_SESSION['role'] === 'admin';
            }
            case 'access_moderator_page': {
                return $_SESSION['role'] === 'moderator' || $_SESSION['role'] === 'admin';
            }
            case 'access_admin_page': {
                return $_SESSION['role'] === 'admin';
            }
            case 'view_dashboard': {
                return $_SESSION['role'] === 'user' || $_SESSION['role'] === 'moderator' || $_SESSION['role'] === 'admin';
            }
            case 'view_users': {
                return $_SESSION['role'] === 'admin';
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

    public function requireRole(string $role): bool {
        return !isset($_SESSION['role']) || $_SESSION['role'] !== $role;
    }

    public function hasRole(string $role): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    public function changeUserRole(int $userId, string $newRole): bool {
        return $this->authRepo->changeUserRole($userId, $newRole);
    }
}