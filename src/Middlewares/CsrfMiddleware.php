<?php

namespace Middlewares;

class CsrfMiddleware
{
    public function handle()
    {
        if(empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = generateRandomToken();
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if(!validateCsrfToken($token)) {
   -            http_response_code(403);
                echo "CSRF token validation failed.";
                exit;
            }
        }
    }
}