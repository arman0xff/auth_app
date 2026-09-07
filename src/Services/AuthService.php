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
}