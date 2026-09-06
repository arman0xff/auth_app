<?php

use DTOs\User\RegisterUserDto;
use Services\UserService;

readonly class UserController
{
    public function __construct(private UserService $userService) {
        
    }

    public function register(): void {
        require __DIR__ . "/../DTOs/User/RegisterUserDto.php";

        $errors = [];
        $_SESSION['message'] = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            if (strlen($name) < 3 || strlen($name) > 32) {
                $errors['name'] = "Wrong name length\n";
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Wrong email format\n";
            } else if($this->userService->checkEmailExist($email)) {
                $errors['email'] = "Email already exists\n";
            } else if (strlen($password) < 3 || strlen($password) > 32) {
                $errors['password'] = "Wrong password length\n";
            } else {
                try {
                    require_once __DIR__ . '/../Mailer.php';

                    $_SESSION = [];

                    session_destroy();

                    $newUserDto = new RegisterUserDto($name, $email, $password);

                    $this->userService->register($newUserDto);

                    $_SESSION['message'] = "Account successfully registered. Please check your email to verify your account.";

                    header('Location: /login');
                    exit;
                } catch (Exception $e) {
                    $errors['button'] = "Account doesn't registered";
                }
            }
        }
        require __DIR__ . '/../Views/Register.php';
    }

    public function login(): void {
        require __DIR__ . '/../Models/User.php';

        $message = "";
        $success = $_SESSION['message'] ?? null;
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

                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }

        require __DIR__ . '/../Views/Login.php';
    }

    public function dashboard(): void {
        $message = "";

        if (!isset($_SESSION["id"])) {
            $message = "You must be logged in to access this page";
        }
        else if(!isset($_SESSION["email_verified_at"]) || $_SESSION["email_verified_at"] == null) {
            $message = "You must verify your email to access this page";
        }

        require __DIR__ . '/../Views/Dashboard.php';
    }

    public function logout(): void {
        $_SESSION = [];

        session_destroy();

        header('Location: /login');
        exit;
    }

    public function verifyEmail(): void {
        $token = $_GET['token'] ?? null;

        if($token == null) {
            header('Location: /register');
            exit;
        }

        if($this->userService->verifyToken($token)) {
            $_SESSION['message'] = "Your email has been verified.";
            
            if($_SESSION['id']) {
                header('Location: /dashboard');
            }
            else {
                header('Location: /login');
            }
        }
        else {
            $message = "Invalid token";
            header('Location: /register');
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

                if($resultArr['status'] == E_RESEND_MAIL_RETURN_CODES::Success) {
                    $success = "Email successfully sent";
                }
                else {
                    $error = match($resultArr['status']) {
                        E_RESEND_MAIL_RETURN_CODES::NotFound => "Email not sent (some error occurred)",
                        E_RESEND_MAIL_RETURN_CODES::RateLimit => "You need to wait about 60 seconds, after sending new email",
                        default => "Email not sent (some error occurred)",
                    };
                }
            }
        }

        require __DIR__ . '/../Views/ResendMail.php';

        exit;
    }
}