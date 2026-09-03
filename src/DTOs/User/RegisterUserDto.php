<?php

namespace DTOs\User;

readonly class registeruserdto
{
    public function __construct(public string $name, public string $email, public string $pass) {

    }
}