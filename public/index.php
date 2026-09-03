<?php
use DTOs\User\registeruserdto;
use DTOs\UserDto;
$request = $_SERVER['REQUEST_URI'];
switch ($request) {
    case '/':
    case '/register':
    {
        require '../src/DBConnection.php';
        require "../src/Auth.php";
        require "../src/DTOs/User/RegisterUserDto.php";

        $auth = new auth($pdo);
        $message = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            if (strlen($name) < 3 || strlen($name) > 32) {
                $message = "Wrong name length\n";
            } else if (!str_contains($email, "@") || $auth->check_email($email)) {
                $message = "Wrong email format\n";
            } else if (strlen($password) < 3 || strlen($password) > 32) {
                $message = "Wrong password length\n";
            } else {
                $new_user_dto = new registeruserdto($name, $email, $password);
                try {
                    $auth->create_user($new_user_dto);
                    header('Location: /login');
                    exit;
                } catch (Exception $e) {
                    $message = "Account doesnt registered";
                }
            }
        }
        require __DIR__ . '/../src/view/Register.php';
        break;
    }
    case '/login': {

        session_start();

        require 'DBConnection.php';
        require 'Auth.php';
        require 'Models/User.php';

        $auth = new auth($pdo);
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            try {
                $user = $auth->login($email, $password);
                $_SESSION['id'] = $user->id;
                $_SESSION['name'] = $user->name;
                header('Location: Dashboard.php');
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../src/view/Login.php';
        break;
    }

    case '/dashboard': {
        require __DIR__ . '/../src/view/Dashboard.php';
        break;
    }
    case '/logout': {
        require __DIR__ . '/../src/view/Logout.php';
    }
}
?>