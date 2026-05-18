<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

require_super_admin();

$search = trim($_GET['search'] ?? '');
$users = get_users($search);

render_app_start('User Management', 'users');
?>
<section class="panel">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Super Admin Only</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">All system users</h3>
            <p class="mt-2 text-sm text-slate-600">Review emails, update profiles, send password resets, and deactivate or reactivate accounts.</p>
        </div>
        <form method="get" class="flex w-full gap-3 lg:w-auto">
            <input type="search" name="search" value="<?= e($search) ?>" class="input-field min-w-0 lg:w-80" placeholder="Search name, email, or role">
            <button type="submit" class="btn-primary">Search</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="px-3 py-3">Name</th>
                    <th class="px-3 py-3">Email</th>
                    <th class="px-3 py-3">Role</th>
                    <th class="px-3 py-3">Account Status</th>
                    <th class="px-3 py-3">Borrower Approval</th>
                    <th class="px-3 py-3">Created</th>
                    <th class="px-3 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users === []): ?>
                    <tr>
                        <td colspan="7" class="px-3 py-8 text-center text-sm text-slate-500">No users found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $user): ?>
                    <?php
                    $isCurrentUser = (int) $user['id'] === (int) (current_user()['id'] ?? 0);
                    $accountStatus = (string) ($user['account_status'] ?? 'active');
                    $nextStatus = $accountStatus === 'active' ? 'inactive' : 'active';
                    ?>
                    <tr>
                        <td class="px-3 py-4 font-medium text-library-ink"><?= e($user['name']) ?></td>
                        <td class="px-3 py-4"><?= e($user['email']) ?></td>
                        <td class="px-3 py-4"><span class="badge <?= e(status_badge_class((string) $user['role'])) ?>"><?= e(format_role((string) $user['role'])) ?></span></td>
                        <td class="px-3 py-4"><span class="badge <?= e(status_badge_class($accountStatus)) ?>"><?= e(ucfirst($accountStatus)) ?></span></td>
                        <td class="px-3 py-4">
                            <?php if (($user['role'] ?? '') === 'borrower'): ?>
                                <span class="badge <?= e(status_badge_class((string) $user['approval_status'])) ?>"><?= e(ucfirst((string) $user['approval_status'])) ?></span>
                            <?php else: ?>
                                <span class="text-sm text-slate-400">Not applicable</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-4"><?= e(format_date((string) $user['created_at'])) ?></td>
                        <td class="px-3 py-4">
                            <div class="flex flex-wrap gap-2">
                                <a href="<?= e(url('admin/users/edit.php?id=' . (int) $user['id'])) ?>" class="btn-secondary px-3 py-2">Edit Profile</a>
                                <form method="post" action="<?= e(url('admin/users/reset-password.php')) ?>" onsubmit="return confirm('Send a temporary password to this user?');">
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <button type="submit" class="btn-secondary px-3 py-2">Send Reset Password</button>
                                </form>
                                <form method="post" action="<?= e(url('admin/users/toggle-status.php')) ?>" onsubmit="return confirm('<?= e($nextStatus === 'active' ? 'Reactivate this account?' : 'Deactivate this account?') ?>');">
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <input type="hidden" name="status" value="<?= e($nextStatus) ?>">
                                    <button type="submit" class="<?= $nextStatus === 'active' ? 'btn-primary' : 'btn-danger' ?> px-3 py-2" <?= $isCurrentUser ? 'disabled title="You cannot deactivate your own account."' : '' ?>>
                                        <?= e($nextStatus === 'active' ? 'Reactivate' : 'Deactivate') ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_app_end();
