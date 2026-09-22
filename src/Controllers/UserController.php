<?php

namespace Controllers;

use DTOs\User\RegisterUserDto;
use DTOs\User\LoginUserDto;
use DTOs\User\ProfileUserDto;
use Services\UserService;
use Services\AuthService;
use Services\PostService;
use Exception;
use E_SEND_MAIL_RETURN_CODES;

readonly class UserController {
    public function __construct(private UserService $userService, private AuthService $authService, private PostService $postService) {
        
    }

    public function register(): void {
        require_once __DIR__ . "/../DTOs/User/RegisterUserDto.php";

        $errors = [];
        unset($_SESSION['message']);
        
        $newUserDto = new RegisterUserDto($_POST['first_name'] ?? '', $_POST['last_name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');

        $result = $this->userService->register($newUserDto);

        if($result->isValid) {
            $_SESSION['message'] = $result->message;
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }

        $errors = is_array($result->value) ? $result->value : ['button' => $result->message];
        $this->showRegisterForm($errors);
    }

    public function showRegisterForm(array $errors = []): void {
        require_once __DIR__ . '/../Views/Register.php';
    }

    public function login(): void {
        require_once __DIR__ . '/../Models/User.php';
        require_once __DIR__ . "/../DTOs/User/LoginUserDto.php";

        $success = $_SESSION['message'] ?? "";
        if($success != null && strlen($_SESSION['message']) == 0) {
            $success = "";
        }

        unset($_SESSION['message']);

        $userDto = new LoginUserDto($_POST['email'] ?? "", $_POST['password'] ?? "");

        $result = $this->userService->login($userDto);

        if(!$result->isValid) {
            $this->showLoginForm($result->message);
            return;
        }

        header('Location: ' . PROFILE_USER_ROUTE);
        exit;
    }

    public function showLoginForm(string $error = "", string $success = ""): void {
        require_once __DIR__ . '/../Views/Login.php';
    }

    public function dashboard(): void {        
        $result = $this->userService->tryOpenDashboard();

        if(!$result->isValid) {
            switch((int)$result->value) {
                case 2: {
                    header('Location: ' . LOGIN_USER_ROUTE);
                    exit;
                }
                default: {
                    $error = $result->message;
                    $userDto = null;
                }
            }
        }
        else {
            $error = "";
            $userDto = $result->value;
        }


        require_once __DIR__ . '/../Views/Dashboard.php';
    }

    public function logout(): void {
        $_SESSION = [];

        session_destroy();

        header('Location: ' . LOGIN_USER_ROUTE);

        exit;
    }

    public function verifyEmail(): void {
        $token = $_GET['token'] ?? null;

        if($token == null) {
            header('Location: ' . REGISTER_USER_ROUTE);
            exit;
        }

        if($this->userService->verifyEmailVerificationToken($token)) {
            $_SESSION['message'] = "Your email has been verified.";
            
            header('Location: ' . $_SESSION['id'] ? DASHBOARD_USER_ROUTE : LOGIN_USER_ROUTE);
        }
        else {
            header('Location: ' . REGISTER_USER_ROUTE);
        }
        exit;
    }

    public function resendMail(): void {
        $error = "";
        $success = "";

        $email = $_POST["email"] ?? null;

        if($email == null) {
            $error = "Email is required";
        }
        else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Wrong email format";
        }
        else {
            $resultArr = $this->userService->resendVerificationMail($email);

            if($resultArr['status'] == E_SEND_MAIL_RETURN_CODES::Success) {
                $success = "Email successfully sent";
            }
            else {
                $error = match($resultArr['status']) {
                    E_SEND_MAIL_RETURN_CODES::NotFound => "Email not sent (some error occurred)",
                    E_SEND_MAIL_RETURN_CODES::RateLimit => "You need to wait about 60 seconds, after sending new email",
                    default => "Email not sent (some error occurred)",
                };
            }
        }

        require_once __DIR__ . '/../Views/ResendMail.php';
    }

    public function forgetPassword(): void {
        $error = "";
        $success = "";
        $email = $_POST["email"] ?? null;

        if($email == null) {
            $error = "Email is required";
        }
        else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Wrong email format";
        }
        else {
            $userId = $this->userService->getIdByEmail($email);

            if($userId == null) {
                $error = "Email doesn't exist";
            }
            else {
                $resultArr = $this->userService->sendResetPasswordMail($userId, $email);
                if($resultArr['status'] == E_SEND_MAIL_RETURN_CODES::Success) {
                    $success = "Email successfully sent";
                }
                else {
                    $error = match($resultArr['status']) {
                        E_SEND_MAIL_RETURN_CODES::NotFound => "Email not sent (some error occurred)",
                        E_SEND_MAIL_RETURN_CODES::RateLimit => "You need to wait about 60 seconds, after sending new email",
                        default => "Email not sent (some error occurred)",
                    };
                }
            }
        }

        $this->showResetPasswordForm($error, $success);
    }

    public function showResetPasswordForm(string $error = "", string $success = "") {
        require_once __DIR__ . '/../Views/ForgetPassword.php';
    }

    public function resetPassword(): void {
        $error = "";
        $success = "";

        $token = $_SESSION["token"] ?? null;

        if($_SESSION["token"] != null) {
            $_SESSION["token"] = null;
        }

        if($token == null) {
            $error = "Token required";
        }
        else {
            if($_POST["new-password"] != $_POST["confirm-new-password"]) {
                $error = "Passwords are not the same";
                $_SESSION["token"] = $token;
            }
            else {
                try {
                    if(!$this->userService->verifyResetPasswordToken($_SESSION["token"])) {
                        $error = "Invalid token";
                    }
                    else if($this->userService->updatePassword($token, $_POST["new-password"])) {
                        $_SESSION['message'] = "Password successfully changed";
                        $_SESSION["token"] = null;
                        header('Location: ' . LOGIN_USER_ROUTE);
                        exit;
                    }
                    else {
                        $error = "Failed to update password";
                    }
                } catch(Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        require_once __DIR__ . '/../Views/ResetPassword.php';
    }

    public function showPasswordResetForm(): void {
        $error = "";
        $success = "";

        $_SESSION["token"] = $_GET["token"] ?? null;

        require_once __DIR__ . '/../Views/ResetPassword.php';
    }

    public function showAdminPanel(): void {
        $role = $this->authService->refreshUserRole($_SESSION['id']);

        if(!$this->authService->can($role, 'access_admin_page')) {
            http_response_code(403);
            $error = "You don't have permission to view this page";
        }

        $users = $this->userService->getAllUsers();

        require_once __DIR__ . '/../Views/AdminPanel.php';
    }

    public function editUserDataInAdminPanel(): void {
        $role = $this->authService->refreshUserRole($_SESSION['id']);

        if(!$this->authService->can($role, 'access_admin_page')) {
            http_response_code(403);
            $error = "You don't have permission to view this page";
        }
    
        $userId = $_POST["user_id"] ?? null;
        $newRole = $_POST["new_role"] ?? null;

        if($userId == null || $newRole == null) {
            $error = "User id and new role are required";
        }
        else if(!$this->authService->can($role, 'manage_users')) {
            $error = "You don't have permission to change user roles";
        }
        else {
            try {
                $this->authService->changeUserRole($userId, $newRole);
                header('Location: ' . ADMIN_PANEL_ROUTE);
                exit;
            } catch(Exception $e) {
                $error = $e->getMessage();
            }
        }

        $users = $this->userService->getAllUsers();

        require_once __DIR__ . '/../Views/AdminPanel.php';
    }

    public function showModeratorPanel(): void {
        $role = $this->authService->refreshUserRole($_SESSION['id']);

        if(!$this->authService->can($role, 'access_moderator_page')) {
            http_response_code(403);
            $error = "You don't have permission to view this page";
        }
        
        $users = $this->userService->getAllUsers();

        require_once __DIR__ . '/../Views/ModeratorPanel.php';
    }

    public function showProfile(): void {
        require_once __DIR__ . '/../DTOs/User/ProfileUserDto.php';

        $error = "";
        if(isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            $_SESSION['error'] = "";
        }

        $profileId = $_GET["id"] ?? $_SESSION['id'] ?? null;

        if(empty($profileId)) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }
        else {
            $userDto = $this->userService->getUserData($profileId);

            if($userDto == null) {
                $error = "Requested profile ID not found";
            }
            else {
                if(isset($userDto->profileImageId)) {
                    $profileImageUrl = $this->userService->getProfileImageUrl($userDto->profileImageId);
                }
                $userPosts = $this->postService->getUserPostsByUserId($profileId);
            }
        }

        require_once __DIR__ . '/../Views/Profile.php';
    }

    public function editProfile(): void {
        if(!isset($_SESSION['id'])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }
        else {
            $userId = $_SESSION['id'];
            $errors = [];
            $success = "";

            try {
                $imageObj = null;
                if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $imageObj = $_FILES['profile_image'];
                }

                $shouldImageDelete = isset($_POST['delete_image']) && $_POST['delete_image'] === '1';

                $userDto = new ProfileUserDto(
                    $userId,
                    $_SESSION['email'] ?? '',
                    $_POST['first_name'] ?? '',
                    $_POST['last_name'] ?? '',
                    $_SESSION['role'] ?? 'user',
                    $_POST['phone'] ?? '',
                    $_POST['location'] ?? '',
                    $_POST['dob'] ?? '',
                    $_POST['bio'] ?? ''
                );
                
                $result = $this->userService->updateUserProfile($userDto, $imageObj, $shouldImageDelete);

                if($result->isValid) {
                    $success = $result->message;
                } else {
                    if(is_array($result->value)) {
                        $errors = $result->value;
                    } else {
                        $errors['default'] = $result->message;
                    }
                }
            } catch(Exception $e) {
                $errors['exception'] = $e->getMessage();
            }

            $userDto = $this->userService->getUserData($userId);
        }

        require_once __DIR__ . '/../Views/EditProfile.php';
    }

    public function showEditProfileForm(): void {
        $userId = $_SESSION['id'];

        if(!isset($userId)) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }

        $userDto = $this->userService->getUserData($userId);

        require_once __DIR__ . '/../Views/EditProfile.php';
    }
}