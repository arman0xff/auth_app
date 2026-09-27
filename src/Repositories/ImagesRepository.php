<?php

namespace Repositories;

use PDO;
use Interfaces\IImagesRepository;

readonly class ImagesRepository implements IImagesRepository {
    public function __construct(private PDO $pdo) {

    }

    public function createMultiplePostImages(int $postId, array $names): int {
        $sql = "INSERT IGNORE INTO `post_images` (post_id, file_name) VALUES (";

        $isFirstDone = false;

        foreach($names as $name) {
            if(!$isFirstDone) {
                $sql = $sql . "?,?)";
                $isFirstDone = true;
            }
            else {
                $sql = $sql . ",(?,?)";
            }
        }

        $sth = $this->pdo->prepare($sql);

        $preparedArr = [];

        foreach($names as $name) {
            $preparedArr[] = $postId;
            $preparedArr[] = $name;
        }

        $sth->execute($preparedArr);

        return $sth->rowCount();
    }

    public function findPostImages(int $postId): array {
        $sql = "SELECT `file_name` FROM `post_images` WHERE `post_id` = :postId";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["postId" => $postId]);
        return $sth->fetchAll(PDO::FETCH_COLUMN);
    }
}