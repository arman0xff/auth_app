<?php

namespace Services;

use DTOs\User\RegisterUserDto;
use Exception;
use Mailer;
use PDO;
use Models\User;

class UserService
{
    public function __construct(private PDO $pdo) {
    }

    public function register(RegisterUserDto $userDto): int {
        $pass_hash = password_hash($userDto->pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO `users` (name, email, password, verification_token) VALUES(:name, :email, :pass, :verification_token)";

        $sth = $this->pdo->prepare($sql);
        $sth->execute([
            "name" => $userDto->name,
            "email" => $userDto->email,
            "pass" => $pass_hash,
            "verification_token" => $userDto->token
        ]);

        //$user = new User($userDto->name, $userDto->email, $userDto->pass);

        Mailer::sendVerificationMail($userDto->email, $userDto->name, $userDto->token);

        return (int)$this->pdo->lastInsertId();
    }
    public function checkEmail(string $email): bool {
        $sql = "SELECT 1 FROM `users` WHERE `email` = :email";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["email" => $email]);

        return $sth->fetch() !== false;
    }

    public function login(string $email, string $password): User {
        $sql = "SELECT `id`, `name`, `password`, `email_verified_at` FROM `users` WHERE `email` = :email";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["email" => $email]);

        $user = $sth->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Invalid email or password");
        }

        return new User((int)$user['id'], $user['name'], $email, $user['email_verified_at']);
    }

    public function verifyToken(string $token): bool
    {
        $sql = "UPDATE `users` SET `email_verified_at` = NOW(), `verification_token` = NULL WHERE `verification_token` = :token AND `email_verified_at` IS NULL";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["token" => $token]);

        return $sth->rowCount() > 0;
    }
}