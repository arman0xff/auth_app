<?php

namespace DTOs\User;

readonly class DashboardUserDto {
    public function __construct(public int $id, public string $email, public string $firstName, public string $lastName, public string $role) {

    }
}