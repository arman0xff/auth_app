<?php

namespace Interfaces;

interface IUserRepository {
    public function create(string $name, string $email, string $pass_hash);
    public function checkEmailExist(string $email);
    public function findByEmail(string $email);
    public function verifyToken(string $token);
    public function generateNewToken(string $email);
    public function createUserVerificationToken(int $userId, string $token);
}