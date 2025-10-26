<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }
}

function requireAdmin(): void
{
    requireLogin();
    $role = strtolower(trim((string)($_SESSION['role'] ?? '')));
    if (!in_array($role, ['admin', 'administrator', '1', 'superadmin'], true)) {
        header('Location: /user/index.php');
        exit();
    }
}
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    $role = strtolower(trim((string)($_SESSION['role'] ?? '')));
    return in_array($role, ['admin', 'administrator', '1', 'superadmin'], true);
}