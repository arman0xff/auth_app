<?php
use DTOs\User\registeruserdto;
use DTOs\UserDto;
$request = $_SERVER['REQUEST_URI'];
switch ($request) {
    case '/view/register':
    {
        require '../src/DBConnection.php';
        require "../src/Auth.php";
        require "../src/DTOs/UserDto.php";

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
                    $message = "You are registered";
                } catch (Exception $e) {
                    $message = "Account doesnt registered";
                }
            }
        }
        require __DIR__ . '/view/register.php';
    }
    case '/view/login':
        require __DIR__ . '/view/login.php';
}
?>

<!DOCTYPE html>
<html lang="en">
    <body>
        <h1>Auth</h1>
    <form method="POST" action="">
        <label> Name: <input type="text" id="name" name="name" required </label>
        <br><label> Email: <input type="text" id="email" name="email" required</label>
        <br><label> Password: <input type="text" id="password" name="password" required</label>
        <br><button type="submit">Register</button>
    </form>
    </body>
</html>
