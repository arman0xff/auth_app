<?php

namespace Interfaces;

interface IUserRepository {
    public function create(string $firstName, string $lastName, string $email, string $pass_hash);
    public function checkEmailExist(string $email);
    public function getIdByEmail(string $email);
    public function findByEmail(string $email);
    public function findByEmailWithRole(string $email);
    public function findById(int $id);
    public function findAllDataById(int $id);
    public function findAll();
    public function updatePassword(string $token, string $password);
    public function updateUserProfile(int $userId, array $data);

    // tokens
    public function createUserToken(int $userId, string $token, string $type);
    public function generateNewToken(string $email, string $type);
    public function deleteToken(string $token);

    //
    public function verifyEmailVerificationToken(string $token);
    public function verifyResetPasswordToken(string $token);
}