<?php

namespace Interfaces;

interface IAuthRepository {
    public function createUserDefaultRole(int $userId);
}