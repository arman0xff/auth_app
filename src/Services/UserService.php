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
use Result;

readonly class UserService {
    public function __construct(private IUserRepository $userRepo, private AuthService $authService) {
    }

    public function register(RegisterUserDto $userDto): Result {
        if(strlen($userDto->firstName) < ACCOUNT_REG_MIN_FIRST_NAME_LEN || strlen($userDto->firstName) > ACCOUNT_REG_MAX_FIRST_NAME_LEN) {
            $errors['first_name'] = "Wrong first name length\n";
        } 
        if(strlen($userDto->lastName) < ACCOUNT_REG_MIN_LAST_NAME_LEN || strlen($userDto->lastName) > ACCOUNT_REG_MAX_LAST_NAME_LEN) {
            $errors['last_name'] = "Wrong last name length\n";
        } 
        if(!filter_var($userDto->email, FILTER_VALIDATE_EMAIL)) { // 255
            $errors['email'] = "Wrong email format\n";
        }
        if($this->checkEmailExist($userDto->email)) {
            $errors['email'] = "Email already exists\n";
        } 
        if(!$this->validatePassword($userDto->password)) {
            $errors['password'] = "Wrong password length\n";
        }

        if(!empty($errors)) {
            return Result::fail("Validation error", $errors);
        }

        $userToken = generateRandomToken();
        $pass_hash = password_hash($userDto->password, PASSWORD_DEFAULT);

        $userId = $this->userRepo->create($userDto->firstName, $userDto->lastName, $userDto->email, $pass_hash);
        
        if($userId == 0) {
            return Result::fail("Failed to create user");
        }

        try {
            $this->authService->createUserDefaultRole($userId);
        } catch (Exception $e) {
            return Result::fail("Failed to create user default role");
        }

        if(!$this->userRepo->createUserToken($userId, $userToken, 'email_verify')) {
            return Result::fail("Failed to create user token");
        }

        require_once __DIR__ . '/../Mailer.php';

        Mailer::sendVerificationMail($userDto->email, $userDto->firstName, $userToken);

        return Result::success("Account successfully registered. Please check your email to verify your account.", $userId);
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

    public function login(LoginUserDto $userDto): Result {
        if(isset($_SESSION["id"])) {
            return Result::fail("You are already logged");
        }

        $resArray = $this->userRepo->findByEmailWithRole($userDto->email);

        if($resArray == null) {
            return Result::fail("Invalid email or password");
        }

        $user = new User(
            (int)$resArray['id'], $resArray['first_name'], $resArray['last_name'], $userDto->email, $resArray['password'], $resArray['role'],
            $resArray['email_verified_at']
        );

        if(!$user || !password_verify($userDto->password, $user->password)) {
            return Result::fail("Invalid email or password");
        }

        if($user->emailVerifiedAt == null) {
            return Result::fail("You need to verify your email");
        }

        session_regenerate_id(true);

        $_SESSION['id'] = $user->id;
        $_SESSION['first_name'] = $user->firstName;
        $_SESSION['last_name'] = $user->lastName;
        $_SESSION['email'] = $user->email;
        $_SESSION['email_verified_at'] = $user->emailVerifiedAt;

        return Result::success("Login successful", $user);
    }

    public function update(string $email): int {
        return $this->userRepo->getIdByEmail($email);
    }

    public function tryOpenDashboard(): Result {
        require_once __DIR__ . '/../DTOs/User/DashboardUserDto.php';

        if(!isset($_SESSION["id"])) {
            return Result::fail("You must be logged in to access this page", 0);
        }
        
        if(!isset($_SESSION["email_verified_at"]) || $_SESSION["email_verified_at"] == null) {
            session_destroy();
            $_SESSION = [];
            return Result::fail("You must verify your email to access this page", 1);
        }

        $role = $this->authService->refreshUserRole($_SESSION['id']);
        
        $userDto = new DashboardUserDto($_SESSION['id'], $_SESSION['email'], $_SESSION['first_name'], $_SESSION['last_name'], $role);

        if(!$this->authService->can($userDto->role, 'view_dashboard')) {
            return Result::fail("You don't have permission to view this page", 2);
        }

        if($this->authService->requireLogin()) {
            return Result::fail("", 2);
        }

        return Result::success("", $userDto);
    }

    public function getUserData(int $userId): Result {
        require_once __DIR__ . '/../DTOs/User/ProfileUserDto.php';

        if(!isset($_SESSION['id'])) {
            return Result::fail("Unauthorized access to profile data");
        }

        $user = $this->userRepo->findAllDataById($userId);

        if($user == null) {
            return Result::fail("User not found");
        }

        $userData = new ProfileUserDto($user['id'], $user['email'], $user['first_name'], $user['last_name'], $user['role'], 
            $user['phone'] ?? "not provided", $user['location'] ?? "not provided", $user['date_of_birth'] ?? "not provided", 
            $user['bio'] ?? "not provided", $user['image_id'] ?? null);

        return Result::success("Successfully fetched user data", $userData);
    }

    public function updateUserProfile(ProfileUserDto $dto, ?array $photo, bool $isImageDelete): Result {
        if(!isset($_SESSION['id']) || $_SESSION['id'] !== $dto->id) {
            $errors['unauthenticated'] = "Unauthorized access to update profile";
        }
        if(strlen($dto->firstName) < ACCOUNT_REG_MIN_FIRST_NAME_LEN || strlen($dto->firstName) > ACCOUNT_REG_MAX_FIRST_NAME_LEN) {
            $errors['first_name'] = "Wrong first name length\n";
        } 
        if(strlen($dto->lastName) < ACCOUNT_REG_MIN_LAST_NAME_LEN || strlen($dto->lastName) > ACCOUNT_REG_MAX_LAST_NAME_LEN) {
            $errors['last_name'] = "Wrong last name length\n";
        }

        if(!empty($errors)) {
            return Result::fail("Validation error", $errors);
        }

        $currentImageId = $this->userRepo->findUserImageId($dto->id);

        $uploadDir = __DIR__ . '/../../public/storage/uploads/';

        if($isImageDelete) {
            if(isset($currentImageId)) {
                $this->deleteOldProfileImage($currentImageId, $uploadDir);
                $currentImageId = null;
            }
        }
        else if(isset($photo) && $photo['error'] === UPLOAD_ERR_OK) {
            $nextFileId = $this->getLastUploadId() + 1;

            $parts = explode(".", $photo['name']);
            $type = end($parts);
            
            if($type != "jpg" && $type != "png") {
                $errors['image'] = "You need to upload jpg or png photos";
                return Result::fail("Validation error", $errors);
            }

            $targetFilePath = $uploadDir . "profile_image_" . $nextFileId . "_" . basename($photo['name']);

            if(!move_uploaded_file($photo['tmp_name'], $targetFilePath)) {
                $errors['image'] = "Failed to upload profile picture";
                return Result::fail("Validation error", $errors);
            }
            else {
                if(isset($currentImageId)) {
                    $this->deleteOldProfileImage($currentImageId, $uploadDir);
                }

                $currentImageId = $nextFileId;
            }
        }

        if(!$this->userRepo->updateUserProfile($dto->id, [
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'phone' => $dto->phone,
            'location' => $dto->location,
            'date_of_birth' => $dto->dateOfBirth,
            'bio' => $dto->bio,
            'image_id' => $currentImageId
        ])) {
            return Result::fail("Server error", $errors['server'] = "Failed to update profile");
        }

        return Result::success("Profile updated successfully");
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

    public function deleteOldProfileImage(int $imageId, string $uploadDir): void {
        if(!is_dir($uploadDir)) {
            return;
        }

        $files = array_diff(scandir($uploadDir), ['.', '..']);
        
        foreach ($files as $file) {
            $formatted = explode('_', $file);
            if(sizeof($formatted) >= 3 && $imageId === (int)$formatted[2]) {
                @unlink($uploadDir . $file);
            }
        }
    }

    public function setImageId(int $userId, ?int $nextFileId): bool {
        return $this->userRepo->setImageId($userId, $nextFileId);
    }

    public function getProfileImageUrl(int $imageId): ?string {
        $uploadDir = __DIR__ . '/../../public/storage/uploads/';
        $files = array_diff(scandir($uploadDir, SCANDIR_SORT_ASCENDING), ['.', '..']);
        foreach ($files as $file) {
            $formatted = explode("_", $file);

            if(file_exists($uploadDir . $file) && sizeof($formatted) >= 3 && $imageId === (int)$formatted[2]) {
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