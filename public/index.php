<?php
require __DIR__ . '/../src/Controllers/AuthController.php';
$authController = new AuthController();

$request = $_SERVER['REQUEST_URI'];

session_start();

switch ($request) {
    case '/':

    case '/register': {
        $authController->register();
        break;
    }
    case '/login': {
        $authController->login();
        break;
    }

    case '/dashboard': {
        $authController->dashboard();
        break;
    }
    case '/logout': {
        $authController->logout();
    }
}
?>