<?php

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generateRandomToken(): string {
    return bin2hex(random_bytes(20));
}

function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

const ACCOUNT_REG_MIN_FIRST_NAME_LEN = 3;
const ACCOUNT_REG_MAX_FIRST_NAME_LEN = 24;

const ACCOUNT_REG_MIN_LAST_NAME_LEN = 3;
const ACCOUNT_REG_MAX_LAST_NAME_LEN = 24;

const ACCOUNT_REG_MIN_PASS_LEN = 3;
const ACCOUNT_REG_MAX_PASS_LEN = 32;

const REGISTER_USER_ROUTE = "/register";
const LOGIN_USER_ROUTE = "/login";
const DASHBOARD_USER_ROUTE = "/dashboard";
const LOGOUT_USER_ROUTE = "/logout";
const VERIFY_EMAIL_ROUTE = "/verify-email";
const RESEND_MAIL_ROUTE = "/resend-mail";
const FORGET_PASSWORD_ROUTE = "/forget-password";
const RESET_PASSWORD_ROUTE = "/reset-password";
const ADMIN_PANEL_ROUTE = "/admin";
const MODERATOR_PANEL_ROUTE = "/moderator";
const PROFILE_USER_ROUTE = "/profile";
const EDIT_PROFILE_ROUTE = "/edit-profile";
const ADD_NEW_POST_ROUTE = PROFILE_USER_ROUTE . "/add-new-post";
const EDIT_POST_ROUTE = PROFILE_USER_ROUTE . "/edit-post";
const DELETE_POST_ROUTE = PROFILE_USER_ROUTE . "/delete-post";

enum E_SEND_MAIL_RETURN_CODES {
    case Success;
    case RateLimit;
    case NotFound;
}