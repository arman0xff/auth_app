<?php

namespace Interfaces;

interface IUserRepository {
    public function create(string $name, string $email, string $pass_hash);
    public function checkEmailExist(string $email);
    public function getIdByEmail(string $email);
    public function findByEmail(string $email);
    public function findByEmailWithRole(string $email);
    public function findAll(): ?array;
    public function updatePassword(string $token, string $password);

    // tokens
    public function createUserToken(int $userId, string $token, string $type);
    public function generateNewToken(string $email, string $type);
    public function deleteToken(string $token);

    //
    public function verifyEmailVerificationToken(string $token);
    public function verifyResetPasswordToken(string $token);
}