<?php

namespace Repositories;

use PDO;
use Interfaces\IPostRepository;
use Exception;
use E_SEND_MAIL_RETURN_CODES;
use Override;

readonly class PostRepository implements IPostRepository {
    public function __construct(private PDO $pdo) {

    }

    public function findUserPostsByUserId(int $userId): ?array {
        $sql = "SELECT * FROM `posts` WHERE `user_id` = :userId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["userId" => $userId]);

        if($sth->fetch() == false) {
            return null;
        }

        return $sth->fetchAll();
    }
}