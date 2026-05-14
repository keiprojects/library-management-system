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

    if (($sessionUser['role'] ?? '') === 'borrower') {
        $freshUser = find_user_by_id((int) ($sessionUser['id'] ?? 0));

        if ($freshUser === null || !user_can_log_in($freshUser)) {
            logout_user();
            session_start();
            flash('error', $freshUser === null ? 'Your borrower account is no longer available.' : borrower_login_block_message($freshUser));
            redirect('login.php');
        }
    }

    refresh_overdue_records();
}

