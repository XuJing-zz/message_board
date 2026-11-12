<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(string $redirect = 'login.php'): void
{
    if (current_user() === null) {
        header('Location: ' . $redirect);
        exit;
    }
}