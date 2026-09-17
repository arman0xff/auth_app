<?php

namespace Services;

use DTOs\User\RegisterUserDto;
use DTOs\User\LoginUserDto;
use DTOs\User\DashboardUserDto;
use DTOs\User\ProfileUserDto;
use Exception;
use Mailer\Mailer;
use Models\User;
use E_SEND_MAIL_RETURN_CODES;
use Interfaces\IPostRepository;
use Helpers;

readonly class PostService {
    public function __construct(private IPostRepository $postRepo) {
    }

    public function addNewPost() {
        $header = $_POST["header"];

        if(empty($header) || strlen($header) < 3 || strlen($header) > 64) {
            throw new Exception("Header length does not correct");    
        }

        $text = $_POST["maintext"];

        if(empty($text) || strlen($text) < 10 || strlen($text) > 300) {
            throw new Exception("Main text length does not correct");    
        }

        $userId = $_SESSION["id"];

        $this->postRepo->addNewPost($userId, $header, $text);
    }

    public function getUserPostsByUserId(int $userId): ?array {
        if(empty($_SESSION[$userId])) {
            throw new Exception("User session not found");
        }

        return $this->postRepo->findUserPostsByUserId($userId);
    }
}