<?php

use DTOs\User\RegisterUserDto;
use DTOs\User\LoginUserDto;

class auth
{
    private PDO $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create_user(RegisterUserDto $user_dto):int {
        if($this->check_email($user_dto->email)) {
            throw new Exception("Email is already in use");
        }

        $pass_hash = password_hash($user_dto->pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password) VALUES(:name, :email, :pass)";
        $stmt = $this->pdo->prepare($sql);
        $sth = $stmt->execute([
            "name" => $user_dto->name,
            "email" => $user_dto->email,
            "pass" => $pass_hash
        ]);

        return $this->pdo->lastInsertId();
    }

    public function check_email(string $email):bool {
        $sql = "SELECT 1 FROM users WHERE email = :email";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["email" => $email]);

        return $sth->fetch() !== false;
    }

    public function login(string $email, string $password):user {
        $sql = "SELECT `id`, `name`, `password` FROM users WHERE email = :email";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["email" => $email]);

        $user = $sth->fetch();
        if (!$user) {
            throw new Exception("user not found");
        }

        if (!password_verify($password, $user['password'])) {
            throw new Exception("Invalid password");
        }

        return new user($user['id'], $user['name'], $email);
    }
}