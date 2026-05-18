<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

require_super_admin();

$userId = (int) ($_GET['id'] ?? 0);
$user = $userId > 0 ? find_user_by_id($userId) : null;

if ($user === null) {
    flash('error', 'User not found.');
    redirect('admin/users/index.php');
}

$currentUserId = (int) (current_user()['id'] ?? 0);
$isCurrentUser = $userId === $currentUserId;
$errors = [];
$form = [
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'account_status' => $user['account_status'] ?? 'active',
];

if (is_post()) {
    $form = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'role' => trim($_POST['role'] ?? ''),
        'account_status' => trim($_POST['account_status'] ?? ''),
    ];

    if ($form['name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (!value_in_options($form['role'], user_role_options())) {
        $errors[] = 'Please choose a valid role.';
    }

    if (!value_in_options($form['account_status'], account_status_options())) {
        $errors[] = 'Please choose a valid account status.';
    }

    if ($isCurrentUser && $form['role'] !== 'super_admin') {
        $errors[] = 'You cannot remove your own super admin role.';
    }

    if ($isCurrentUser && $form['account_status'] !== 'active') {
        $errors[] = 'You cannot deactivate your own account.';
    }

    if ($errors === []) {
        $result = update_user_profile($userId, $form);

        if ($result['success']) {
            if ($isCurrentUser) {
                sync_session_user($form + ['id' => $userId]);
            }

            flash('success', $result['message']);
            redirect('admin/users/index.php');
        }

        $errors[] = $result['message'];
    }
}

render_app_start('Edit User Profile', 'users');
?>
<section class="panel max-w-3xl">
    <div class="mb-6">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Super Admin User Form</p>
        <h3 class="mt-2 text-2xl font-semibold text-library-ink">Update user profile</h3>
        <p class="mt-2 text-sm text-slate-600">Use this form to correct names, find or update emails, change roles, and control login access.</p>
    </div>

    <?php if ($errors !== []): ?>
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="grid gap-1">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="label-text">Full Name</label>
            <input type="text" id="name" name="name" class="input-field" value="<?= e($form['name']) ?>">
        </div>
        <div class="md:col-span-2">
            <label for="email" class="label-text">Email Address</label>
            <input type="email" id="email" name="email" class="input-field" value="<?= e($form['email']) ?>">
        </div>
        <div>
            <label for="role" class="label-text">Role</label>
            <select id="role" name="role" class="input-field" <?= $isCurrentUser ? 'disabled' : '' ?>>
                <?php foreach (user_role_options() as $role): ?>
                    <option value="<?= e($role) ?>" <?= selected($form['role'], $role) ?>><?= e(format_role($role)) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($isCurrentUser): ?>
                <input type="hidden" name="role" value="super_admin">
                <p class="mt-2 text-xs text-slate-500">Your own super admin role cannot be removed.</p>
            <?php endif; ?>
        </div>
        <div>
            <label for="account_status" class="label-text">Account Status</label>
            <select id="account_status" name="account_status" class="input-field" <?= $isCurrentUser ? 'disabled' : '' ?>>
                <?php foreach (account_status_options() as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected($form['account_status'], $status) ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($isCurrentUser): ?>
                <input type="hidden" name="account_status" value="active">
                <p class="mt-2 text-xs text-slate-500">You cannot deactivate your own account.</p>
            <?php endif; ?>
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Update User</button>
            <a href="<?= e(url('admin/users/index.php')) ?>" class="btn-secondary">Back to Users</a>
        </div>
    </form>
</section>
<?php
render_app_end();
