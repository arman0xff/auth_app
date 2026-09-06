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
    case REGISTER_USER_ROUTE: {
        $userController->register();
        break;
    }
    case LOGIN_USER_ROUTE: {
        $userController->login();
        break;
    }
    case DASHBOARD_USER_ROUTE: {
        $userController->dashboard();
        break;
    }
    case LOGOUT_USER_ROUTE: {
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
    case FORGET_PASSWORD_ROUTE: {
        $userController->forgetPassword();
        break;
    }
    case RESET_PASSWORD_ROUTE: {
        $userController->resetPassword();
        break;
    }
}
?>