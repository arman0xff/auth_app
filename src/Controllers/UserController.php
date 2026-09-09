<?php

namespace Controllers;

use DTOs\User\RegisterUserDto;
use DTOs\User\LoginUserDto;
use Services\UserService;
use Services\AuthService;
use Exception;
use E_SEND_MAIL_RETURN_CODES;

readonly class UserController {
    public function __construct(private UserService $userService, private AuthService $authService) {
        
    }

    public function register(): void {
        require_once __DIR__ . "/../DTOs/User/RegisterUserDto.php";

        $errors = [];
        unset($_SESSION['message']);
        
        $newUserDto = new RegisterUserDto($_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');

        try {
            if($this->userService->register($newUserDto, $errors)) {
                $_SESSION['message'] = "Account successfully registered. Please check your email to verify your account.";
                header('Location: ' . LOGIN_USER_ROUTE);
                exit;
            }
        } catch (Exception) {
            $errors['button'] = "Account doesn't registered";
        }

        $this->showRegisterForm($errors);
    }

    public function showRegisterForm(array $errors = []): void {
        require_once __DIR__ . '/../Views/Register.php';
    }

    public function login(): void {
        require_once __DIR__ . '/../Models/User.php';

        $error = "";
        $success = $_SESSION['error'] ?? "";
        if($success != null && strlen($_SESSION['error']) == 0) {
            $success = "";
        }

        unset($_SESSION['error']);

        $userDto = new LoginUserDto($_POST['email'] ?? "", $_POST['password'] ?? "");

        try {
            $this->userService->login($userDto);

            header('Location: ' . DASHBOARD_USER_ROUTE);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->showLoginForm();
    }

    public function showLoginForm(): void {
        require_once __DIR__ . '/../Views/Login.php';
    }

    public function dashboard(): void {
        $error = "";

        try {
            $this->userService->tryOpenDashboard($error);
        } catch (Exception $e) {
            $error = $e->getMessage();
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
            
            if($_SESSION['id']) {
                header('Location: ' . DASHBOARD_USER_ROUTE);
            }
            else {
                header('Location: ' . LOGIN_USER_ROUTE);
            }
        }
        else {
            $message = "Invalid token";
            header('Location: ' . REGISTER_USER_ROUTE);
        }
        exit;
    }

    public function resendMail(): void {
        $error = "";
        $success = "";

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            if(!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $error = "Invalid session token";
            }
            else {
                $email = $_POST["email"] ?? null;

                if($email == null) {
                    $error = "Email is required";
                }
                else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
            }
        }

        require_once __DIR__ . '/../Views/ResendMail.php';
    }

    public function forgetPassword(): void {
        $error = "";
        $success = "";

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            if(!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $error = "Invalid session token";
            }
            else {
                $email = $_POST["email"] ?? null;

                if($email == null) {
                    $error = "Email is required";
                }
                else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
            }
        }

        require_once __DIR__ . '/../Views/ForgetPassword.php';
    }

    public function resetPassword(): void {
        $error = "";
        $success = "";

        if($_SERVER["REQUEST_METHOD"] == "GET") {
            $_SESSION["token"] = $_GET["token"] ?? null;
        }
        else if($_SERVER["REQUEST_METHOD"] == "POST") {
            if(!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $error = "Invalid session token";
            }
            else  {
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
            }
        }

        require_once __DIR__ . '/../Views/ResetPassword.php';
    }

    public function showAdminPanel(): void {
        if(!isset($_SESSION['id'])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }
        else {
            $users = [];
            $_SESSION['role'] = $this->authService->refreshUserRole($_SESSION['id']);

            if(!$this->authService->can('access_admin_page')) {
                http_response_code(403);
                $error = "You don't have permission to view this page";
            }
            else if($_SERVER["REQUEST_METHOD"] == "GET") {
                $users = $this->userService->getAllUsers();
            }
            else {
                if($_SERVER["REQUEST_METHOD"] == "POST") {
                    if(!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                        $error = "Invalid session token";
                    }
                    else {
                        $userId = $_POST["user_id"] ?? null;
                        $newRole = $_POST["new_role"] ?? null;

                        if($userId == null || $newRole == null) {
                            $error = "User id and new role are required";
                        }
                        else if(!$this->authService->can('manage_users')) {
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
                    }
                }
            }
        }

        require_once __DIR__ . '/../Views/AdminPanel.php';
    }
    public function showModeratorPanel(): void {
        if(!isset($_SESSION['id'])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }
        else {
            $users = [];
            $_SESSION['role'] = $this->authService->refreshUserRole($_SESSION['id']);

            if(!$this->authService->can('access_moderator_page')) {
                http_response_code(403);
                $error = "You don't have permission to view this page";
            }
            else if($_SERVER["REQUEST_METHOD"] == "GET") {
                $users = $this->userService->getAllUsers();
            }
            else {
                $error = "Invalid request method";
            }
        }

        require_once __DIR__ . '/../Views/ModeratorPanel.php';
    }
}