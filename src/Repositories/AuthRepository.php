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
        $sql = "INSERT INTO `user_roles` (user_id) VALUES (:user_id)";
        $sth = $this->pdo->prepare($sql);

        $sth->execute(["user_id" => $userId]);
    }
}