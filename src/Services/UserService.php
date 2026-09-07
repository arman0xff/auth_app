<?php

namespace Services;

use DTOs\User\RegisterUserDto;
use Exception;
use Mailer\Mailer;
use Models\User;
use E_SEND_MAIL_RETURN_CODES;
use Interfaces\IUserRepository;

readonly class UserService {
    public function __construct(private IUserRepository $userRepo, private AuthService $authService) {
    }

    public function register(RegisterUserDto $userDto): int {
        $userToken = generateToken();
        $pass_hash = password_hash($userDto->pass, PASSWORD_DEFAULT);

        $userId = $this->userRepo->create($userDto->name, $userDto->email, $pass_hash);
        
        $this->authService->createUserRole($userId);

        $this->userRepo->createUserToken($userId, $userToken, 'email_verify');

        require_once __DIR__ . '/../Mailer.php';

        Mailer::sendVerificationMail($userDto->email, $userDto->name, $userToken);

        return $userId;
    }

    public function checkEmailExist(string $email): bool {
        return $this->userRepo->checkEmailExist($email);
    }

    public function getIdByEmail(string $email): ?int {
        return $this->userRepo->getIdByEmail($email);
    }

    public function validatePassword(string $password): bool {
        return strlen($password) >= ACCOUNT_REG_MIN_PASS_LEN && strlen($password) <= ACCOUNT_REG_MAX_PASS_LEN;
    }

    public function updatePassword(string $token, string $password): bool {
        if(!$this->validatePassword($password)) {
            throw new Exception("Wrong password length");
        }
    
        $result = $this->userRepo->updatePassword($token, password_hash($password, PASSWORD_DEFAULT));
    
        if($result) {
            $this->userRepo->deleteToken($token);
        }

        return $result;
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

    public function update(string $email): int {
        return $this->userRepo->getIdByEmail($email);
    }

    // tokens

    public function verifyEmailVerificationToken(string $token): bool {
        return $this->userRepo->verifyEmailVerificationToken($token);
    }

    public function resendVerificationMail(string $email): array {
        $resultArr = $this->userRepo->generateNewToken($email, 'email_verify');
        if($resultArr['status'] == E_SEND_MAIL_RETURN_CODES::Success) {
            require_once __DIR__ . '/../Mailer.php';
            Mailer::sendVerificationMail($email, $_SESSION["name"] ?? "Mysterious stranger", $resultArr['token']);
        }

        return $resultArr;
    }

    public function sendResetPasswordMail(int $userId, string $email): array {
        $resultArr = $this->userRepo->generateNewToken($email, 'pass_reset');

        if($resultArr['status'] == E_SEND_MAIL_RETURN_CODES::Success) {
            require_once __DIR__ . '/../Mailer.php';
            Mailer::sendResetPasswordMail($email, $_SESSION["name"] ?? "Mysterious stranger", $resultArr['token']);
        }

        return $resultArr;
    }

    public function verifyResetPasswordToken(string $token): bool {
        return $this->userRepo->verifyResetPasswordToken($token);
    }
}