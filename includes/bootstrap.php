<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
date_default_timezone_set(APP_TIMEZONE);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/library.php';
require_once __DIR__ . '/layout.php';

if (is_logged_in()) {
    refresh_overdue_records();
}

