<?php

namespace Repositories;

use PDO;
use Interfaces\IUserRepository;
use Exception;
use E_SEND_MAIL_RETURN_CODES;
use Override;

readonly class UserRepository implements IUserRepository {
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

    public function getIdByEmail(string $email): ?int {
        $sql = "SELECT `id` FROM `users` WHERE `email` = :email";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["email" => $email]);

        $row = $sth->fetch();

        if($row == null) {
            return null;
        }

        return $row['id'];
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

    public function findByEmailWithRole(string $email): ?array {
        $sql = "SELECT `u`.`id`, `u`.`name`, `u`.`password`, `u`.`email_verified_at`, `dur`.`role`
            FROM `users` AS u JOIN `user_roles` AS ur ON `u`.`id` = `ur`.`user_id` 
            JOIN `defined_user_roles` AS dur ON `ur`.`role_id` = `dur`.`id`
            WHERE `u`.`email` = :email";
            
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["email" => $email]);

        $user = $sth->fetch();

        if($user == null) {
            return null;
        }

        return $user; 
    }

    public function updatePassword(string $token, string $password): bool {
        $sql = "UPDATE `users` JOIN `user_tokens` ON `users`.`id` = `user_tokens`.`user_id` 
            SET `users`.`password` = :password WHERE `user_tokens`.`token` = :token AND `user_tokens`.`type` = 'pass_reset'";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["password" => $password, "token" => $token]);

        return $sth->rowCount() > 0; 
    }

    public function deleteToken(string $token): bool {
        $sql = "DELETE FROM `user_tokens` WHERE `token` = :token";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["token" => $token]);

        return $sth->rowCount() > 0; 
    }

    // tokens

    public function createUserToken(int $userId, string $token, string $type): bool {
        $sql = "INSERT INTO `user_tokens` (user_id, token, type) VALUES (:user_id, :token, :type)";
        $sth = $this->pdo->prepare($sql);
        return $sth->execute(["user_id" => $userId, "token" => $token, "type" => $type]);
    }

    // email verification

    public function verifyEmailVerificationToken(string $token): bool {
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

    public function generateNewToken(string $email, string $type): array {
        $token = generateToken();

        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->beginTransaction();

            $sql = "SELECT NULL AS token_sent_at FROM `user_tokens` JOIN `users` ON `user_tokens`.`user_id` = `users`.`id` WHERE `email` = :email 
                AND `token_sent_at` > NOW() - INTERVAL 1 MINUTE AND `type` = :type";
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["email" => $email, "type" => $type]);

            $result = $sth->fetch();
            
            if(!empty($result)) {
                $this->pdo->rollBack();

                return [
                    'status' => E_SEND_MAIL_RETURN_CODES::RateLimit
                ];
            }

            if($type == "email_verify") {
                $sql = "INSERT INTO `user_tokens` (user_id, token, type) SELECT `id`, :token, :type_select FROM `users` WHERE `email` = :email AND `email_verified_at` IS NULL 
                    AND NOT EXISTS(SELECT 1 FROM `user_tokens` WHERE `user_id` = `users`.`id` AND `type` = :type_check AND `token_sent_at` > NOW() - INTERVAL 1 MINUTE)";
            }
            else if($type == "pass_reset") {
                $sql = "INSERT INTO `user_tokens` (user_id, token, type) SELECT `id`, :token, :type_select FROM `users` WHERE `email` = :email 
                    AND NOT EXISTS(SELECT 1 FROM `user_tokens` WHERE `user_id` = `users`.`id` AND `type` = :type_check AND `token_sent_at` > NOW() - INTERVAL 15 MINUTE)";
            }
            else {
                $this->pdo->rollBack();

                return [
                    'status' => E_SEND_MAIL_RETURN_CODES::NotFound
                ];
            }
            
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["token" => $token, "email" => $email, "type_select" => $type, "type_check" => $type]);

            $result = $sth->rowCount() > 0;

            if($result == false) {
                $this->pdo->rollBack();

                return ['status' => E_SEND_MAIL_RETURN_CODES::NotFound];
            }

            $sql = "DELETE `user_tokens` FROM `user_tokens` JOIN `users` ON `user_tokens`.`user_id` = `users`.`id` 
                WHERE `users`.`email` = :email AND `user_tokens`.`token` != :curr_token AND `user_tokens`.`type` = :type";
            $sth = $this->pdo->prepare($sql);
            $sth->execute(["email" => $email, "curr_token" => $token, "type" => $type]);

            $this->pdo->commit();
            return [
                'status' => E_SEND_MAIL_RETURN_CODES::Success, 
                'token' => $token
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // password reset

    public function verifyResetPasswordToken(string $token): array|bool {
        $sql = "SELECT 1 FROM `user_tokens` WHERE `token` = :token AND `token_sent_at` >= NOW() - INTERVAL 15 MINUTE AND `type` = 'pass_reset'";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["token" => $token]);

        return $sth->fetch();
    }
}