<?php

require 'db_connection.php';
require 'auth.php';
require 'DTOs/User/registeruser_dto.php';

use DTOs\User\registeruserdto;

$auth = new auth($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $registerUserDto = new registeruserdto($name, $email, $password);

    try {
        $auth->create_user($registerUserDto);
        header('Location: login.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<body>
    <?php if ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="post" action="register.php">
        <label> Name: <input type="text" id="name" name="name" required><br><br> </label>
        <label> Email: <input type="email" id="email" name="email" required><br><br></label>
        <label> Password: <input type="password" id="password" name="password" required><br><br> </label>

        <button type="submit">Register</button>
    </form>
</body>
</html>