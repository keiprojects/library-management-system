<?php

declare(strict_types=1);

/**
 * Returns the logged-in user stored in the session.
 *
 * @return array{id:int,name:string,email:string,role:string}|null
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Checks whether a user is logged in.
 */
function is_logged_in(): bool
{
    return current_user() !== null;
}

/**
 * Saves the important user details in the session after login.
 */
function login_user(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
}

/**
 * Updates the session copy of the user after profile edits.
 */
function sync_session_user(array $user): void
{
    if (!isset($_SESSION['user'])) {
        return;
    }

    $_SESSION['user']['name'] = $user['name'];
    $_SESSION['user']['email'] = $user['email'];
    $_SESSION['user']['role'] = $user['role'];
}

/**
 * Destroys the current login session.
 */
function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

/**
 * Sends logged-out visitors to the login page.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

/**
 * Protects admin-only pages (admin and super admin).
 */
function require_admin(): void
{
    require_login();

    if (!in_array((current_user()['role'] ?? ''), ['admin', 'super_admin'], true)) {
        flash('error', 'You do not have permission to access that page.');
        redirect('student/dashboard.php');
    }
}


/**
 * Protects super-admin-only pages.
 */
function require_super_admin(): void
{
    require_login();

    if ((current_user()['role'] ?? '') !== 'super_admin') {
        flash('error', 'Only super admin accounts can access that page.');
        redirect('admin/dashboard.php');
    }
}

/**
 * Protects borrower-only pages.
 */
function require_borrower(): void
{
    require_login();

    if ((current_user()['role'] ?? '') !== 'borrower') {
        flash('error', 'You do not have permission to access that page.');
        redirect('admin/dashboard.php');
    }
}

/**
 * Stops logged-in users from reopening the login or register pages.
 */
function redirect_if_authenticated(): void
{
    $user = current_user();

    if ($user === null) {
        return;
    }

    if (in_array($user['role'], ['admin', 'super_admin'], true)) {
        redirect('admin/dashboard.php');
    }

    redirect('student/dashboard.php');
}
