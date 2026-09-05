<?php

namespace Services;

use DTOs\User\RegisterUserDto;
use Exception;
use Mailer\Mailer;
use PDO;
use Models\User;

require_once __DIR__ . '/../Mailer.php';

class UserService
{
    public function __construct(private PDO $pdo) {
    }

    public function register(RegisterUserDto $userDto): int {
        $pass_hash = password_hash($userDto->pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO `users` (name, email, password) VALUES(:name, :email, :pass)";

        $sth = $this->pdo->prepare($sql);
        $sth->execute([
            "name" => $userDto->name,
            "email" => $userDto->email,
            "pass" => $pass_hash
        ]);

        //$user = new User($userDto->name, $userDto->email, $userDto->pass);

        $userId = (int)$this->pdo->lastInsertId();

        $this->createUserVerificationToken($userId, $userDto->token);

        Mailer::sendVerificationMail($userDto->email, $userDto->name, $userDto->token);

        return $userId;
    }
    public function checkEmailExist(string $email): bool {
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

    public function verifyToken(string $token): bool {
        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->pdo->beginTransaction();
            $sql = "UPDATE `users` JOIN `user_tokens` ON `users`.`id` = `user_tokens`.`user_id` SET `users`.`email_verified_at` = NOW() WHERE `user_tokens`.`token` = :token 
                AND `user_tokens`.`type` = 'email_verify' AND `users`.`email_verified_at` IS NULL AND `user_tokens`.`token_sent_at` > NOW() - INTERVAL 1 HOUR";

            $sth = $this->pdo->prepare($sql);
            $sth->execute(["token" => $token]);

            $result = $sth->rowCount() > 0;

            if($result == 0) {
                $this->pdo->rollBack();
                return false;
            }

            $sql = "DELETE FROM `user_tokens` WHERE `token` = :token AND `type` = 'email_verify'";
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["token" => $token]);

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function generateNewToken(string $email): ?string {
        $token = generateToken();

        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO `user_tokens` (user_id, token, type) SELECT `id`, :token, 'email_verify' FROM `users` WHERE `email` = :email AND `email_verified_at` IS NULL 
                AND NOT EXISTS(SELECT 1 FROM `user_tokens` WHERE `user_id` = `users`.`id` AND `type` = 'email_verify' AND `token_sent_at` > NOW() - INTERVAL 1 MINUTE)";
    
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["token" => $token, "email" => $email]);

            $result = $sth->rowCount() > 0;

            if($result == 0) {
                $this->pdo->rollBack();
                return null;
            }

            $sql = "DELETE `user_tokens` FROM `user_tokens` JOIN `users` ON `user_tokens`.`user_id` = `users`.`id` 
                WHERE `users`.`email` = :email AND `user_tokens`.`token` != :curr_token AND `user_tokens`.`type` = 'email_verify'";
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["email" => $email, "curr_token" => $token]);

            $this->pdo->commit();
            return $token;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function resendVerificationMail(string $email): bool {
        $token = $this->generateNewToken($email);

        if($token == null) {
            return false;
        }

        Mailer::sendVerificationMail($email, $_SESSION["name"] ?? "Mysterious stranger", $token);
        return true;
    }

    //

    public function createUserVerificationToken(int $userId, string $token): bool {
        $sql = "INSERT INTO `user_tokens` (user_id, token, type) VALUES (:user_id, :token, 'email_verify')";
        $sth = $this->pdo->prepare($sql);
        return $sth->execute(["user_id" => $userId, "token" => $token]);
    }
}