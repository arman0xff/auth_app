<?php

namespace DTOs\User;

readonly class LoginUserDto {
    public function __construct(public string $email, public string $password) {

    }
}