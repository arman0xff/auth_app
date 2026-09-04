<?php

use Services\UserService;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/DBConnection.php';
require_once __DIR__ . '/../src/Services/UserService.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';
require_once __DIR__ . '/../src/helpers.php';

$authController = new UserController(new UserService($pdo));

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
    case VERIFY_EMAIL_ROUTE: {
        $authController->verifyEmail();
    }
}
?>