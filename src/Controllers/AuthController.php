<?php

use DTOs\User\RegisterUserDto;

class AuthController
{
    public function __construct(private Auth $auth) {
        
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
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $this->auth->check_email($email)) {
                $message = "Wrong email format\n";
            } else if (strlen($password) < 3 || strlen($password) > 32) {
                $message = "Wrong password length\n";
            } else {
                try {
                    require_once __DIR__ . '/../Mailer.php';

                    $token = bin2hex(random_bytes(20));
                    $new_user_dto = new RegisterUserDto($name, $email, $password, $token);

                    $this->auth->create_user($new_user_dto);

                    $link = "localhost:8000/email-verify?token=" . $token;

                    $verification_message = 'Your verification link is ' . $link;

                    Mailer::sendMail($email, 'Hello, ' . $name . '!', $verification_message);

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
                $user = $this->auth->login($email, $password);
                $_SESSION['id'] = $user->id;
                $_SESSION['name'] = $user->name;
                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }

        require __DIR__ . '/../view/Login.php';
    }

    public function dashboard(): void {
        if (!isset($_SESSION["id"])) {
            header("Location: /login");
            exit;
        }

        require __DIR__ . '/../view/Dashboard.php';
    }

    public function logout(): void {
        $_SESSION = [];

        session_destroy();

        header('Location: /login');
        exit;
    }

    public function verifyEmail(): bool {
        $token = $_GET['token'] ?? null;

        if($token == null) {
            header('Location: /register');
            exit;
        }

        if($this->auth->verifyToken($token)) {
            $_SESSION['flash_message'] = "Your email has been verified";
            header('Location: /login');
        }
        else {
            $_SESSION['flash_message'] = "Invalid token";
            header('Location: /register');
        }
        exit;
    }
}