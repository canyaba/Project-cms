<?php

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function requireAuth(): void
{
    startAppSession();

    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function redirectIfAuthenticated(string $destination = 'dashboard.php'): void
{
    startAppSession();

    if (!empty($_SESSION['user_id'])) {
        header('Location: ' . $destination);
        exit();
    }
}

function loginUser(array $user): void
{
    startAppSession();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
}

function logoutUser(string $destination = 'index.php'): void
{
    startAppSession();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool)($params['secure'] ?? false),
            (bool)($params['httponly'] ?? true)
        );
    }

    session_destroy();
    header('Location: ' . $destination);
    exit();
}
