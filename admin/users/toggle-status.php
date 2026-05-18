<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

require_super_admin();

if (!is_post()) {
    redirect('admin/users/index.php');
}

$userId = (int) ($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$user = $userId > 0 ? find_user_by_id($userId) : null;

if ($user === null) {
    flash('error', 'User not found.');
    redirect('admin/users/index.php');
}

if ($userId === (int) (current_user()['id'] ?? 0)) {
    flash('error', 'You cannot deactivate your own account.');
    redirect('admin/users/index.php');
}

if (!value_in_options($status, account_status_options())) {
    flash('error', 'Invalid account status.');
    redirect('admin/users/index.php');
}

$result = update_user_account_status($userId, $status);
flash($result['success'] ? 'success' : 'error', $result['message']);
redirect('admin/users/index.php');
