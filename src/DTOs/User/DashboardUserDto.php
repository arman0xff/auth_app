<?php

namespace DTOs\User;

readonly class DashboardUserDto {
    public function __construct(public int $id, public string $email, public string $name, public string $role) {

    }
}