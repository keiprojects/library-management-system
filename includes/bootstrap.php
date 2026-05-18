<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
date_default_timezone_set(APP_TIMEZONE);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/library.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/verification.php';
require_once __DIR__ . '/layout.php';

if (is_logged_in()) {
    $sessionUser = current_user();
    $freshUser = find_user_by_id((int) ($sessionUser['id'] ?? 0));

    if ($freshUser === null || !user_can_log_in($freshUser)) {
        $message = $freshUser === null
            ? 'Your account is no longer available.'
            : ((($freshUser['role'] ?? '') === 'borrower') ? borrower_login_block_message($freshUser) : 'Your account has been deactivated. Please contact the super admin.');

        logout_user();
        session_start();
        flash('error', $message);
        redirect('login.php');
    }

    refresh_overdue_records();
}

