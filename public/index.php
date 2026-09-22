<?php

session_save_path(__DIR__ . "/storage/sessions");

use Controllers\UserController;
use Services\UserService;
use Services\AuthService;
use Controllers\PostController;
use Services\PostService;
use Middlewares\CsrfMiddleware;

use Repositories\UserRepository;
use Repositories\AuthRepository;
use Repositories\PostRepository;

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

require_once __DIR__ . '/../src/Controllers/PostController.php';
require_once __DIR__ . '/../src/Interfaces/IPostRepository.php';
require_once __DIR__ . '/../src/Repositories/PostRepository.php';
require_once __DIR__ . '/../src/Services/PostService.php';

$authRepo = new AuthRepository($pdo);
$userRepo = new UserRepository($pdo);
$postRepo = new PostRepository($pdo);

$authService = new AuthService(new AuthRepository($pdo));
$userService = new UserService($userRepo, $authService);
$postService = new PostService($postRepo, $authService);

$userController = new UserController($userService, $authService, $postService);
$postController = new PostController($postService, $userService);

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
    case '/': {

    }
    case REGISTER_USER_ROUTE: {
        if(isset($_SESSION["id"])) {
            header('Location: ' . DASHBOARD_USER_ROUTE);
            exit;
        }

        if($requestMethod === "POST") {
            $userController->register();
        }
        else if($requestMethod === "GET") {
            $userController->showRegisterForm();
        }
        else {
            http_response_code(405);
            echo "Method not allowed.";
        }
        break;
    }
    case LOGIN_USER_ROUTE: {
        if($requestMethod === "POST") {
            $userController->login();
        }
        else if($requestMethod === "GET") {
            if(isset($_SESSION["id"]) && empty($_GET["redirect"])) {
                header('Location: ' . DASHBOARD_USER_ROUTE);
                exit;
            }

            $userController->showLoginForm();
        }
        else {
            http_response_code(405);
            echo "Method not allowed.";
        }
        break;
    }
    case DASHBOARD_USER_ROUTE: {
        if(!isset($_SESSION["id"])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }

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
        if($requestMethod === "POST") {
            $userController->resendMail();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case FORGET_PASSWORD_ROUTE: {
        if($requestMethod === "POST") {
            $userController->forgetPassword();
        }
        else if($requestMethod === "GET") {
            $userController->showResetPasswordForm();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case RESET_PASSWORD_ROUTE: {
        if($requestMethod === "POST") {
            $userController->resetPassword();
        }
        else if($requestMethod === "GET") {
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

        if($requestMethod === "POST") {
            $userController->showAdminPanel();
        }
        else if($requestMethod === "GET") {
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

        if($requestMethod === "GET") {
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
    case ADD_NEW_POST_ROUTE: {
        if(!isset($_SESSION['id'])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }
        
        if($requestMethod === "POST") {
            $postController->addNewPost();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case EDIT_POST_ROUTE: {
        if(!isset($_SESSION['id'])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }
        
        if($requestMethod === "POST") {
            $postController->updatePost();
        }
        else if($requestMethod === "GET") {
            $postController->showProfileWithEditablePost();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case DELETE_POST_ROUTE: {
        if(!isset($_SESSION['id'])) {
            header('Location: ' . LOGIN_USER_ROUTE);
            exit;
        }
        
        if($requestMethod === "POST") {
            $postController->deletePost();
        }
        else {
            http_response_code(405);
            echo("Method not allowed.");
        }
        break;
    }
    case EDIT_PROFILE_ROUTE: {
        if($requestMethod === "POST") {
            $userController->editProfile();
        }
        else if($requestMethod === "GET") {
            $userController->showEditProfileForm();
        }
        else {
            http_response_code(405);
            echo "Method not allowed.";
        }
        break;
    }
    case POSTS_ROUTE: {
        if($requestMethod === "GET") {
            $postController->showAllPosts();
        }
        else {
            http_response_code(405);
            echo "Method not allowed.";
        }
        break;
    }

    default: {
        http_response_code(404);
        echo "No route found: " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
}
?>