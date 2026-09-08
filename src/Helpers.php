<?php

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generateToken(): string {
    return bin2hex(random_bytes(20));
}

const ACCOUNT_REG_MIN_NAME_LEN = 3;
const ACCOUNT_REG_MAX_NAME_LEN = 24;

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

enum E_SEND_MAIL_RETURN_CODES {
    case Success;
    case RateLimit;
    case NotFound;
}