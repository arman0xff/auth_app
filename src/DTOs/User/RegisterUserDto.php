<?php

namespace DTOs\User;

readonly class RegisterUserDto
{
    public function __construct(public string $name, public string $email, public string $pass) {

    }
}