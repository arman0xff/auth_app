<?php

namespace Services;

use DTOs\User\RegisterUserDto;
use Exception;
use Mailer\Mailer;
use Models\User;
use E_RESEND_MAIL_RETURN_CODES;
use Interfaces\IUserRepository;

class UserService
{
    public function __construct(private IUserRepository $userRepo) {
    }

    public function register(RegisterUserDto $userDto): int {
        $userToken = generateToken();
        $pass_hash = password_hash($userDto->pass, PASSWORD_DEFAULT);

        $userId = $this->userRepo->create($userDto->name, $userDto->email, $pass_hash);

        $this->createUserVerificationToken($userId, $userToken);

        require_once __DIR__ . '/../Mailer.php';

        Mailer::sendVerificationMail($userDto->email, $userDto->name, $userToken);

        return $userId;
    }

    public function checkEmailExist(string $email): bool {
        return $this->userRepo->checkEmailExist($email);
    }

    public function login(string $email, string $password): User {
        $resArray = $this->userRepo->findByEmail($email);

        if($resArray == null) {
            throw new Exception("Invalid email or password");
        }

        $user = new User((int)$resArray['id'], $resArray['name'], $email, $resArray['password'], $resArray['email_verified_at']);

        if (!$user || !password_verify($password, $user->password)) {
            throw new Exception("Invalid email or password");
        }

        return $user;
    }

    public function verifyToken(string $token): bool {
        return $this->userRepo->verifyToken($token);
    }

    public function generateNewToken(string $email): array {
        return $this->userRepo->generateNewToken($email);
    }

    public function resendVerificationMail(string $email): array {
        $resultArr = $this->generateNewToken($email);

        if($resultArr['status'] == E_RESEND_MAIL_RETURN_CODES::Success) {
            require_once __DIR__ . '/../Mailer.php';
            Mailer::sendVerificationMail($email, $_SESSION["name"] ?? "Mysterious stranger", $resultArr['token']);
        }

        return $resultArr;
    }

    //

    public function createUserVerificationToken(int $userId, string $token): bool {
        return $this->userRepo->createUserVerificationToken($userId, $token);
    }
}