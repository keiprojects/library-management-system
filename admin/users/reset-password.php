<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

require_super_admin();

if (!is_post()) {
    redirect('admin/users/index.php');
}

$userId = (int) ($_POST['id'] ?? 0);
$user = $userId > 0 ? find_user_by_id($userId) : null;

if ($user === null) {
    flash('error', 'User not found.');
    redirect('admin/users/index.php');
}

$temporaryPassword = generate_temporary_password();
$passwordResult = set_user_password($userId, $temporaryPassword);

if (!$passwordResult['success']) {
    flash('error', $passwordResult['message']);
    redirect('admin/users/index.php');
}

$emailResult = send_password_reset_email($user, $temporaryPassword);

if ($emailResult['success']) {
    flash('success', 'Temporary password sent to ' . $user['email'] . '.');
} else {
    flash('success', 'Password reset created, but email could not be sent: ' . $emailResult['message'] . ' Temporary password: ' . $temporaryPassword);
}

redirect('admin/users/index.php');
