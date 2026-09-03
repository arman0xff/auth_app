<?php

namespace DTOs;

readonly class UserDto
{
    public function __construct(public string $name, public string $email, public string $pass) {

    }
}