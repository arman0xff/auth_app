<?php

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generateToken(): string {
    return bin2hex(random_bytes(20));
}

const ACCOUNT_REG_MIN_NAME_LEN = 3;
const ACCOUNT_REG_MAX_NAME_LEN = 24;

const ACCOUNT_REG_MIN_PASS_LEN = 3;
const ACCOUNT_REG_MAX_PASS_LEN = 32;

const VERIFY_EMAIL_ROUTE = '/verify-email';
const RESEND_MAIL_ROUTE = '/resend-mail';

enum E_RESEND_MAIL_RETURN_CODES {
    case Success;
    case RateLimit;
    case NotFound;
}