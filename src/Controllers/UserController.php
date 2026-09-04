<?php

use DTOs\User\RegisterUserDto;
use JetBrains\PhpStorm\NoReturn;
use Services\UserService;

readonly class UserController
{
    public function __construct(private UserService $userService) {
        
    }

    public function register(): void {
        require __DIR__ . "/../DTOs/User/RegisterUserDto.php";

        $message = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            if (strlen($name) < 3 || strlen($name) > 32) {
                $message = "Wrong name length\n";
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Wrong email format\n";
            } else if($this->userService->checkEmailExist($email)) {
                $message = "Email already exists\n";
            } else if (strlen($password) < 3 || strlen($password) > 32) {
                $message = "Wrong password length\n";
            } else {
                try {
                    require_once __DIR__ . '/../Mailer.php';

                    $newUserDto = new RegisterUserDto($name, $email, $password, generateToken());

                    $this->userService->register($newUserDto);

                    header('Location: /login');
                    exit;
                } catch (Exception $e) {
                    $message = "Account doesnt registered";
                }
            }
        }
        require __DIR__ . '/../view/Register.php';
    }

    public function login(): void {
        require __DIR__ . '/../Models/User.php';

        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            try {
                $user = $this->userService->login($email, $password);

                if(!$user->isVerified()) {
                    $message = "Your account is not verified yet";
                }
                else {
                    $_SESSION['id'] = $user->id;
                    $_SESSION['name'] = $user->name;
                    $_SESSION['email'] = $user->email;
                    header('Location: /dashboard');
                    exit;
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }

        require __DIR__ . '/../view/Login.php';
    }

    public function dashboard(): void {
        if (!isset($_SESSION["id"])) {
            $_SESSION["message"] = "You must be logged in to access this page";
            header("Location: /login");
            exit;
        }

        require __DIR__ . '/../view/Dashboard.php';
    }

    #[NoReturn]
    public function logout(): void {
        $_SESSION = [];

        session_destroy();

        header('Location: /login');
        exit;
    }

    #[NoReturn]
    public function verifyEmail(): void {
        $token = $_GET['token'] ?? null;

        if($token == null) {
            header('Location: /register');
            exit;
        }

        if($this->userService->verifyToken($token)) {
            $message = "Your email has been verified";
            header('Location: /login');
        }
        else {
            $message = "Invalid token";
            header('Location: /register');
        }
        exit;
    }

    public function resendMail(): void {
        $message = '';

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Wrong email format";
            }
            else if ($this->userService->resendVerificationMail($email)) {
                $message = "Email successfully sent";
            } else {
                $message = "Email not sent (some error occurred)";
            }
        }

        require __DIR__ . '/../view/ResendMail.php';

        exit;
    }
}