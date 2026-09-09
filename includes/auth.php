<?php
require_once __DIR__ . '/functions.php';

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    static $user = null;

    if (!is_logged_in()) {
        return null;
    }

    if ($user === null) {
        $stmt = db()->prepare('SELECT id, name, email, role, skills FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;

        if ($user === null) {
            logout_user();
            session_start();
            set_flash('error', 'Your account is no longer available.');
            redirect('login.php');
        }
    }

    return $user;
}

function current_role(): ?string
{
    return current_user()['role'] ?? null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role']    = $user['role'];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function dashboard_path(?string $role = null): string
{
    $role = $role ?? current_role();

    switch ($role) {
        case 'admin':
            return 'admin/dashboard.php';
        case 'freelancer':
            return 'freelancer/dashboard.php';
        case 'client':
            return 'client/dashboard.php';
        default:
            return 'index.php';
    }
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_role(string $role): void
{
    require_login();

    if (current_role() !== $role) {
        set_flash('error', 'You do not have access to that page.');
        redirect(dashboard_path());
    }
}

function attempt_login(string $email, string $password, ?string $role = null): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return null;
    }

    if ($role !== null && $user['role'] !== $role) {
        return null;
    }

    return $user;
}
