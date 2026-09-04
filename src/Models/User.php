<?php

class User
{
    public function __construct (
        public int $id, public string $name, public string $email,
        public ?string $emailVerifiedAt = null
    ) {}

    public function isVerified():bool {
        return $this->emailVerifiedAt !== null;
    }
}