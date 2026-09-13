<?php

session_save_path(__DIR__ . "/../storage/sessions");

use Controllers\UserController;
use Services\UserService;
use Services\AuthService;

use Repositories\UserRepository;
use Repositories\AuthRepository;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/DBConnection.php';
require_once __DIR__ . '/../src/Helpers.php';
require_once __DIR__ . '/../src/Repositories/migrations/migration.php';

$migration = new Migration($pdo);
$migration->migrate();

require_once __DIR__ . '/../src/Interfaces/IUserRepository.php';
require_once __DIR__ . '/../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../src/Services/UserService.php';
require_once __DIR__ . '/../src/Interfaces/IAuthRepository.php';
require_once __DIR__ . '/../src/Repositories/AuthRepository.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';

$userRepo = new UserRepository($pdo);
$authService = new AuthService(new AuthRepository($pdo));
$userService = new UserService($userRepo, $authService);
$userController = new UserController($userService, $authService);

session_start();

if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateRandomToken();
}

switch (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
    case '/':
    case REGISTER_USER_ROUTE: {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $userController->register();
        }
        else if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $userController->showRegisterForm();
        }
        else {
            http_response_code(405);
            echo "Method not allowed.";
        }
        break;
    }
    case LOGIN_USER_ROUTE: {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $userController->login();
        }
        else if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $userController->showLoginForm();
        }
        else {
            http_response_code(405);
            echo "Method not allowed.";
        }
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
    case ADMIN_PANEL_ROUTE: {
        $userController->showAdminPanel();
        break;
    }
    case MODERATOR_PANEL_ROUTE: {
        $userController->showModeratorPanel();
        break;
    }
    default: {
        http_response_code(404);
        echo "No route found.";
    }
}
?>