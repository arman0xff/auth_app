<?php

namespace Repositories;

use PDO;
use Interfaces\IUserRepository;
use Exception;
use E_RESEND_MAIL_RETURN_CODES;

class UserRepository implements IUserRepository {
    public function __construct(private PDO $pdo) {

    }

    public function create(string $name, string $email, string $pass_hash): int {
        $sql = "INSERT INTO `users` (name, email, password) VALUES(:name, :email, :pass)";

        $sth = $this->pdo->prepare($sql);
        $sth->execute([
            "name" => $name,
            "email" => $email,
            "pass" => $pass_hash
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function checkEmailExist(string $email): bool {
        $sql = "SELECT 1 FROM `users` WHERE `email` = :email";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["email" => $email]);

        return $sth->fetch() !== false;
    }

    public function findByEmail(string $email): ?array {
        $sql = "SELECT `id`, `name`, `password`, `email_verified_at` FROM `users` WHERE `email` = :email";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["email" => $email]);

        $user = $sth->fetch();

        if($user == null) {
            return null;
        }

        return $user; 
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

    public function generateNewToken(string $email): array {
        $token = generateToken();

        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->beginTransaction();

            $sql = "SELECT NULL AS token_sent_at FROM `user_tokens` JOIN `users` ON `user_tokens`.`user_id` = `users`.`id` WHERE `email` = :email 
                AND `token_sent_at` > NOW() - INTERVAL 1 MINUTE AND `type` = 'email_verify'";
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["email" => $email]);

            $result = $sth->fetch();
            
            if(!empty($result)) {
                $this->pdo->rollBack();

                return [
                    'status' => E_RESEND_MAIL_RETURN_CODES::RateLimit
                ];
            }

            $sql = "INSERT INTO `user_tokens` (user_id, token, type) SELECT `id`, :token, 'email_verify' FROM `users` WHERE `email` = :email AND `email_verified_at` IS NULL 
                AND NOT EXISTS(SELECT 1 FROM `user_tokens` WHERE `user_id` = `users`.`id` AND `type` = 'email_verify' AND `token_sent_at` > NOW() - INTERVAL 1 MINUTE)";
    
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["token" => $token, "email" => $email]);

            $result = $sth->rowCount() > 0;

            if($result == false) {
                $this->pdo->rollBack();

                return ['status' => E_RESEND_MAIL_RETURN_CODES::NotFound];
            }

            $sql = "DELETE `user_tokens` FROM `user_tokens` JOIN `users` ON `user_tokens`.`user_id` = `users`.`id` 
                WHERE `users`.`email` = :email AND `user_tokens`.`token` != :curr_token AND `user_tokens`.`type` = 'email_verify'";
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["email" => $email, "curr_token" => $token]);

            $this->pdo->commit();
            return [
                'status' => E_RESEND_MAIL_RETURN_CODES::Success, 
                'token' => $token
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function createUserVerificationToken(int $userId, string $token): bool {
        $sql = "INSERT INTO `user_tokens` (user_id, token, type) VALUES (:user_id, :token, 'email_verify')";
        $sth = $this->pdo->prepare($sql);
        return $sth->execute(["user_id" => $userId, "token" => $token]);
    }
}