<?php
require_once __DIR__ . '/../src/DBConnection.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

$authController = new AuthController(new Auth($pdo));

session_start();

switch (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
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