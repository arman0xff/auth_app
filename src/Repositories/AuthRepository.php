<?php

namespace Repositories;

use PDO;
use Interfaces\IAuthRepository;
use Exception;
use E_SEND_MAIL_RETURN_CODES;
use Override;

class AuthRepository implements IAuthRepository {
    public function __construct(private PDO $pdo) {

    }

    public function createUserDefaultRole(int $userId): void {
        $sql = "INSERT INTO `user_roles` (user_id, role_id) VALUES (:user_id, 1)";
        $sth = $this->pdo->prepare($sql);

        $sth->execute(["user_id" => $userId]);
    }

    public function changeUserRole(int $userId, string $newRole): bool {
        $sql = "UPDATE `user_roles` SET `role_id` = (SELECT id FROM `defined_user_roles` WHERE role = :role) WHERE `user_id` = :user_id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["role" => $newRole, "user_id" => $userId]);

        $rowCount = $sth->rowCount();

        if($rowCount == 0) {
            throw new Exception("Role not found");
        }

        return $rowCount > 0;
    }

    public function getUserRoleById(int $userId): ?string {
        $sql = "SELECT `defined_user_roles`.role FROM `user_roles` JOIN `defined_user_roles` ON `user_roles`.role_id = `defined_user_roles`.id WHERE `user_roles`.user_id = :user_id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["user_id" => $userId]);

        $row = $sth->fetch();

        if($row == null) {
            return null;
        }

        return $row['role'];
    }

    public function isRoleExists(string $role): bool {
        $sql = "SELECT 1 FROM `defined_user_roles` WHERE `role` = :role";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["role" => $role]);

        return $sth->fetch() != false;
    }
}