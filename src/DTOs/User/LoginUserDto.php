<?php

namespace DTOs\User;

readonly class LoginUserDto
{
    public function __construct(public int $id, public string $name, public string $email) {

    }
}