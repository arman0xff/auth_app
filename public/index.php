<?php

session_save_path(__DIR__ . "/storage/sessions");

use Controllers\UserController;
use Services\UserService;
use Services\AuthService;
use Middlewares\CsrfMiddleware;

use Repositories\UserRepository;
use Repositories\AuthRepository;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/DBConnection.php';
require_once __DIR__ . '/../src/Helpers.php';

session_start();

require_once __DIR__ . '/../src/Middlewares/CsrfMiddleware.php';

$csrfMiddleware = new CsrfMiddleware();
$csrfMiddleware->handle();

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

switch (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
    case '/': {

    }
    case REGISTER_USER_ROUTE: {
        if (isset($_SESSION["id"])) {
            header('Location: ' . DASHBOARD_USER_ROUTE);
            exit;
        }

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
        if (isset($_SESSION["id"]) && empty($_GET["redirect"])) {
            header('Location: ' . DASHBOARD_USER_ROUTE);
            exit;
        }
        
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
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $userController->resendMail();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case FORGET_PASSWORD_ROUTE: {
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $userController->forgetPassword();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case RESET_PASSWORD_ROUTE: {
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $userController->resetPassword();
        }
        else if($_SERVER["REQUEST_METHOD"] == "GET") {
            $userController->showPasswordResetForm();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case ADMIN_PANEL_ROUTE: {
        if(!isset($_SESSION['id'])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $userController->showAdminPanel();
        }
        else if($_SERVER["REQUEST_METHOD"] == "GET") {
            $userController->editUserDataInAdminPanel();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case MODERATOR_PANEL_ROUTE: {
        if(!isset($_SESSION['id'])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }

        if($_SERVER["REQUEST_METHOD"] == "GET") {
            $userController->showModeratorPanel();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case PROFILE_USER_ROUTE: {
        $userController->showProfile();
        break;
    }
    case EDIT_PROFILE_ROUTE: {
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $userController->editProfile();
        }
        else if($_SERVER["REQUEST_METHOD"] === "GET") {
            require_once __DIR__ . '/../src/Views/EditProfile.php';
        }
        else {
            http_response_code(405);
            echo "Method not allowed.";
        }
        break;
    }
    default: {
        http_response_code(404);
        echo "No route found.";
    }
}
?>