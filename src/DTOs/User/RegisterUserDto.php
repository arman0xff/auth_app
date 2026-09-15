<?php

namespace DTOs\User;

readonly class RegisterUserDto {
    public function __construct(public string $firstName, public string $lastName, public string $email, public string $password) {

    }
}