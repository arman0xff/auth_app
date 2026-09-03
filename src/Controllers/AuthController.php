<?php

use DTOs\User\RegisterUserDto;

class AuthController
{
    public function __construct(private Auth $auth) {
        
    }

    public function register(): void
    {
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
                $new_user_dto = new RegisterUserDto($name, $email, $password);
                try {
                    $this->auth->create_user($new_user_dto);

                    header('Location: /login');
                    exit;
                } catch (Exception $e) {
                    $message = "Account doesnt registered";
                }
            }
        }
        require __DIR__ . '/../view/Register.php';
    }

    public function login(): void
    {
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

    public function dashboard(): void
    {
        if (!isset($_SESSION["id"])) {
            header("Location: /login");
            exit;
        }

        require __DIR__ . '/../view/Dashboard.php';
    }

    public function logout(): void
    {
        $_SESSION = [];

        session_destroy();

        header('Location: /login');
        exit;
    }
}