<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    flash('error', 'The verification link is missing or invalid.');
    redirect('login.php');
}

$result = verify_user_email($token);
flash($result['success'] ? 'success' : 'error', $result['message']);
redirect('login.php');
