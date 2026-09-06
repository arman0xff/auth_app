<?php

use Controllers\UserController;
use Services\UserService;
use Repositories\UserRepository;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/DBConnection.php';
require_once __DIR__ . '/../src/Helpers.php';

require_once __DIR__ . '/../src/Interfaces/IUserRepository.php';
require_once __DIR__ . '/../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../src/Services/UserService.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';

$userRepo = new UserRepository($pdo);
$userService = new UserService($userRepo);
$userController = new UserController($userService);

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
    case RESEND_MAIL_ROUTE: {
        $userController->resendMail();
        break;
    }
}
?>