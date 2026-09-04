<?php

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

const VERIFY_EMAIL_ROUTE = '/verify-email';