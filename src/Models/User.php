<?php

namespace Models;

class User {
    public function __construct (
        public int $id, public string $firstName, public string $lastName, public string $email, public string $password,
        public ?string $role = null,
        public ?string $emailVerifiedAt = null
    ) {}

    public function isVerified():bool {
        return $this->emailVerifiedAt !== null;
    }
}