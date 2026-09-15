<?php

namespace DTOs\User;

class ProfileUserDto {
    public function __construct(public int $id, public string $email, public string $firstName, public string $lastName, public string $role,
                            public ?string $phone = null, public ?string $location = null, public ?string $dateOfBirth = null, public ?string $bio = null,
                            public ?string $profileImageUrl = null) {

    }
}