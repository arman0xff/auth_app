<?php

namespace Controllers;

use DTOs\User\RegisterUserDto;
use Services\UserService;
use Exception;
use E_SEND_MAIL_RETURN_CODES;
readonly class UserController {
    public function __construct(private UserService $userService) {
        
    }

    public function register(): void {
        require_once __DIR__ . "/../DTOs/User/RegisterUserDto.php";

        $errors = [];
        $_SESSION['message'] = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            if (strlen($name) < ACCOUNT_REG_MIN_NAME_LEN || strlen($name) > ACCOUNT_REG_MAX_NAME_LEN) {
                $errors['name'] = "Wrong name length\n";
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // 255
                $errors['email'] = "Wrong email format\n";
            } else if($this->userService->checkEmailExist($email)) {
                $errors['email'] = "Email already exists\n";
            } else if ($this->userService->validatePassword($password)) {
                $errors['password'] = "Wrong password length\n";
            } else {
                try {
                    require_once __DIR__ . '/../Mailer.php';

                    $_SESSION = [];

                    $newUserDto = new RegisterUserDto($name, $email, $password);

                    $this->userService->register($newUserDto);

                    $_SESSION['message'] = "Account successfully registered. Please check your email to verify your account.";

                    header('Location: ' . LOGIN_USER_ROUTE);
                    exit;
                } catch (Exception $e) {
                    $errors['button'] = "Account doesn't registered";
                }
            }
        }
        require_once __DIR__ . '/../Views/Register.php';
    }

    public function login(): void {
        require_once __DIR__ . '/../Models/User.php';

        $message = "";
        $success = $_SESSION['message'] ?? "";
        if($success != null && strlen($_SESSION['message']) == 0) {
            $success = "";
        }

        unset($_SESSION['message']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            try {
                $user = $this->userService->login($email, $password);

                $_SESSION['id'] = $user->id;
                $_SESSION['name'] = $user->name;
                $_SESSION['email'] = $user->email;
                $_SESSION['email_verified_at'] = $user->emailVerifiedAt;

                header('Location: ' . DASHBOARD_USER_ROUTE);
                exit;
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }

        require_once __DIR__ . '/../Views/Login.php';
    }

    public function dashboard(): void {
        $message = "";

        if (!isset($_SESSION["id"])) {
            $message = "You must be logged in to access this page";
        }
        else if(!isset($_SESSION["email_verified_at"]) || $_SESSION["email_verified_at"] == null) {
            $message = "You must verify your email to access this page";
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

        require_once __DIR__ . '/../Views/ResendMail.php';

        exit;
    }

    public function forgetPassword(): void {
        $error = "";
        $success = "";

        if($_SERVER["REQUEST_METHOD"] == "POST") {
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

        require_once __DIR__ . '/../Views/ForgetPassword.php';

        exit;
    }

    public function resetPassword(): void {
        $error = "";
        $success = "";

        if($_SERVER["REQUEST_METHOD"] == "GET") {
            $_SESSION["token"] = $_GET["token"] ?? null;
        }
        else if($_SERVER["REQUEST_METHOD"] == "POST") {
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
                        if($this->userService->updatePassword($token, $_POST["new-password"])) {
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

        require_once __DIR__ . '/../Views/ResetPassword.php';

        exit;
    }
}