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
use Interfaces\IUserRepository;
use Helpers;

readonly class UserService {
    public function __construct(private IUserRepository $userRepo, private AuthService $authService) {
    }

    public function register(RegisterUserDto $userDto, array &$errors): int {
        if (strlen($userDto->firstName) < ACCOUNT_REG_MIN_FIRST_NAME_LEN || strlen($userDto->firstName) > ACCOUNT_REG_MAX_FIRST_NAME_LEN) {
            $errors['name'] = "Wrong first name length\n";
            return 0;
        } 
        if (strlen($userDto->firstName) < ACCOUNT_REG_MIN_LAST_NAME_LEN || strlen($userDto->firstName) > ACCOUNT_REG_MAX_LAST_NAME_LEN) {
            $errors['name'] = "Wrong last name length\n";
            return 0;
        } 
        if (!filter_var($userDto->email, FILTER_VALIDATE_EMAIL)) { // 255
            $errors['email'] = "Wrong email format\n";
            return 0;
        }
        if($this->checkEmailExist($userDto->email)) {
            $errors['email'] = "Email already exists\n";
            return 0;
        } 
        if (!$this->validatePassword($userDto->password)) {
            $errors['password'] = "Wrong password length\n";
            return 0;
        }

        $userToken = generateRandomToken();
        $pass_hash = password_hash($userDto->password, PASSWORD_DEFAULT);

        $userId = $this->userRepo->create($userDto->firstName, $userDto->lastName, $userDto->email, $pass_hash);
        
        if($userId == 0) {
            throw new Exception("Failed to create user");
        }

        try {
            $this->authService->createUserDefaultRole($userId);
        } catch (Exception $e) {
            throw new Exception("Failed to create user default role");
        }

        if(!$this->userRepo->createUserToken($userId, $userToken, 'email_verify')) {
            throw new Exception("Failed to create user token");
        }

        require_once __DIR__ . '/../Mailer.php';

        Mailer::sendVerificationMail($userDto->email, $userDto->firstName, $userToken);

        return $userId;
    }

    public function checkEmailExist(string $email): bool {
        return $this->userRepo->checkEmailExist($email);
    }

    public function getIdByEmail(string $email): ?int {
        return $this->userRepo->getIdByEmail($email);
    }

    public function getAllUsers(): ?array {
        return $this->userRepo->findAll();
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

    public function login(LoginUserDto $userDto): User {
        $resArray = $this->userRepo->findByEmailWithRole($userDto->email);

        if($resArray == null) {
            throw new Exception("Invalid email or password");
        }

        $user = new User(
            (int)$resArray['id'], $resArray['name'], $userDto->email, $resArray['password'], $resArray['role'],
            $resArray['email_verified_at']
        );

        if (!$user || !password_verify($userDto->password, $user->password)) {
            throw new Exception("Invalid email or password");
        }

        session_regenerate_id(true);

        $_SESSION['id'] = $user->id;
        $_SESSION['name'] = $user->name;
        $_SESSION['email'] = $user->email;
        $_SESSION['email_verified_at'] = $user->emailVerifiedAt;

        return $user;
    }

    public function update(string $email): int {
        return $this->userRepo->getIdByEmail($email);
    }

    public function tryOpenDashboard(&$error = ""): ?DashboardUserDto {
        require_once __DIR__ . '/../DTOs/DashboardUserDto.php';

        if (!isset($_SESSION["id"])) {
            $error = "You must be logged in to access this page";
            return null;
        }
        
        if(!isset($_SESSION["email_verified_at"]) || $_SESSION["email_verified_at"] == null) {
            $error = "You must verify your email to access this page";
            return null;
        }

        $role = $this->authService->refreshUserRole($_SESSION['id']);
        
        $userDto = new DashboardUserDto($_SESSION['id'], $_SESSION['email'], $_SESSION['first_name'], $_SESSION['last_name'], $role);

        if(!$this->authService->can($userDto->role, 'view_dashboard')) {
            $error = "You don't have permission to view this page";
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }

        if($this->authService->requireLogin()) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }

        return $userDto;
    }

    public function getUserData(int $userId): ?ProfileUserDto {
        require_once __DIR__ . '/../DTOs/User/ProfileUserDto.php';

        if(!isset($_SESSION['id'])) {
            throw new Exception("Unauthorized access to profile data");
        }

        $user = $this->userRepo->findAllDataById($userId);

        $name = explode(" ", $user['name']);

        $userData = new ProfileUserDto($user['id'], $user['email'], $name[0], $name[1] ?? "not provided", $user['role'], 
            $user['phone'] ?? "not provided", $user['location'] ?? "not provided", $user['date_of_birth'] ?? "not provided", $user['bio'] ?? "not provided");

        return $userData;
    }

    public function updateUserProfile(ProfileUserDto $dto, ?array $photo, $isImageDelete): bool {
        if(!isset($_SESSION['id']) || $_SESSION['id'] !== $dto->id) {
            throw new Exception("Unauthorized access to update profile");
        }

        if (strlen($dto->firstName) < ACCOUNT_REG_MIN_FIRST_NAME_LEN || strlen($dto->firstName) > ACCOUNT_REG_MAX_FIRST_NAME_LEN) {
            $errors['name'] = "Wrong first name length\n";
            return 0;
        } 
        if (strlen($dto->firstName) < ACCOUNT_REG_MIN_LAST_NAME_LEN || strlen($dto->firstName) > ACCOUNT_REG_MAX_LAST_NAME_LEN) {
            $errors['name'] = "Wrong last name length\n";
            return 0;
        }
        if($this->checkEmailExist($dto->email)) {
            $errors['email'] = "Email already exists\n";
            return 0;
        }

        $uploadDir = __DIR__ . '/../../public/storage/uploads/';

        if($isImageDelete) {
            $this->deleteOldProfileImage($dto->id, $uploadDir);
        }

        if(isset($photo) && $photo['error'] === UPLOAD_ERR_OK) {
            $this->deleteOldProfileImage($dto->id, $uploadDir);
            
            $nextFileId = $this->getLastUploadId() + 1;
            $targetFilePath = $uploadDir . "profile_image_" . $nextFileId . "_" . basename($photo['name']);

            if(!move_uploaded_file($photo['tmp_name'], $targetFilePath)) {
                throw new Exception("Failed to upload profile picture");
            }
        }

        return $this->userRepo->updateUserProfile($dto->id, [
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'phone' => $dto->phone,
            'location' => $dto->location,
            'date_of_birth' => $dto->dateOfBirth,
            'bio' => $dto->bio
        ]);
    }

    public function getLastUploadId(): int {
        $uploadDir = __DIR__ . '/../../public/storage/uploads/';
        $files = array_diff(scandir($uploadDir), ['.', '..']);
        $files = array_filter($files, function($file) {
            return str_starts_with($file, "profile_image_");
        });

        if(sizeof($files) < 1) {
            return 0;
        }

        natsort($files);
        $formatted = explode("_", end($files));
        $lastId = (int)$formatted[2];

        return $lastId;
    }

    public function deleteOldProfileImage($userId, $uploadDir): void {
        if(!is_dir($uploadDir)) {
            return;
        }

        $files = array_diff(scandir($uploadDir), ['.', '..']);
        foreach ($files as $file) {
            $formatted = explode('_', $file);
            if (sizeof($formatted) >= 3 && $userId === (int)$formatted[2]) {
                @unlink($uploadDir . $file);
            }
        }
    }

    public function getProfileImageUrl(int $imageId): ?string {
        $uploadDir = __DIR__ . '/../../public/storage/uploads/';
        $files = array_diff(scandir($uploadDir, SCANDIR_SORT_ASCENDING), ['.', '..']);
        foreach ($files as $file) {
            $formatted = explode("_", $file);

            if (file_exists($uploadDir . $file) && sizeof($formatted) >= 3 && $imageId === (int)$formatted[2]) {
                return '/storage/uploads/' . $file;
            }
        }
        return null;
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