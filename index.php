<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    if ((current_user()['role'] ?? '') === 'admin') {
        redirect('admin/dashboard.php');
    }

    redirect('student/dashboard.php');
}

redirect('login.php');

