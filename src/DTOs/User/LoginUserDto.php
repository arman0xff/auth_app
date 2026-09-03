<?php

namespace DTOs\User;

readonly class loginuserdto
{
    public function __construct(public int $id, public string $name, public string $email) {

    }
}