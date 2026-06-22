<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function require_login(): void
{
    start_secure_session();
    if (empty($_SESSION['admin_user_id'])) {
        redirect_to('login.php');
    }
}

function current_admin_username(): string
{
    start_secure_session();
    return (string) ($_SESSION['admin_username'] ?? 'Admin');
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = (int) $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    csrf_token();

    return true;
}

function logout_admin(): void
{
    start_secure_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}
