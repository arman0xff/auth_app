<?php

use Services\UserService;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/DBConnection.php';
require_once __DIR__ . '/../src/Services/UserService.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';
require_once __DIR__ . '/../src/Helpers.php';

$userController = new UserController(new UserService($pdo));

session_start();

switch (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
    case '/':
    case '/register': {
        $userController->register();
        break;
    }
    case '/login': {
        $userController->login();
        break;
    }
    case '/dashboard': {
        $userController->dashboard();
        break;
    }
    case '/logout': {
        $userController->logout();
        break;
    }
    case VERIFY_EMAIL_ROUTE: {
        $userController->verifyEmail();
        break;
    }
    case '/resend-mail': {
        $userController->resendMail();
        break;
    }
}
?>