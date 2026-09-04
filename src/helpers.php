<?php

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generateToken(): string {
    return bin2hex(random_bytes(20));
}

const VERIFY_EMAIL_ROUTE = '/verify-email';
const RESEND_MAIL_ROUTE = '/resend-mail';