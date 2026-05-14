<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_admin();

$stats = get_admin_dashboard_stats();
$recentActivity = get_recent_activity();

render_app_start('Admin Dashboard', 'dashboard');
?>
<section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Total Books</p>
        <p class="mt-4 text-4xl font-semibold text-library-ink"><?= e((string) $stats['total_books']) ?></p>
        <p class="mt-2 text-sm text-slate-500">All physical copies recorded in the library.</p>
    </div>
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Available Books</p>
        <p class="mt-4 text-4xl font-semibold text-library-ink"><?= e((string) $stats['available_books']) ?></p>
        <p class="mt-2 text-sm text-slate-500">Copies that are ready to borrow right now.</p>
    </div>
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Borrowed Books</p>
        <p class="mt-4 text-4xl font-semibold text-library-ink"><?= e((string) $stats['borrowed_books']) ?></p>
        <p class="mt-2 text-sm text-slate-500">Active loans across borrowed and overdue records.</p>
    </div>
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Overdue Books</p>
        <p class="mt-4 text-4xl font-semibold text-amber-700"><?= e((string) $stats['overdue_books']) ?></p>
        <p class="mt-2 text-sm text-slate-500">Borrow records that passed the due date.</p>
    </div>
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Pending Borrowers</p>
        <p class="mt-4 text-4xl font-semibold text-amber-700"><?= e((string) $stats['pending_borrowers']) ?></p>
        <p class="mt-2 text-sm text-slate-500">Borrower registrations waiting for student ID approval.</p>
    </div>
</section>

<section class="mt-8 grid gap-8 xl:grid-cols-[1.3fr_0.7fr]">
    <div class="panel overflow-hidden">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Recent Activity</p>
                <h3 class="mt-2 text-2xl font-semibold text-library-ink">Latest Borrowing Transactions</h3>
            </div>
            <a href="<?= e(url('admin/transactions/return.php')) ?>" class="btn-secondary">Manage Returns</a>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                        <th class="px-3 py-3">Borrower</th>
                        <th class="px-3 py-3">Book</th>
                        <th class="px-3 py-3">Borrowed</th>
                        <th class="px-3 py-3">Due</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Penalty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($recentActivity === []): ?>
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500">No transactions yet.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($recentActivity as $record): ?>
                        <tr>
                            <td class="px-3 py-4">
                                <p class="font-semibold text-library-ink"><?= e($record['name']) ?></p>
                            </td>
                            <td class="px-3 py-4"><?= e($record['title']) ?></td>
                            <td class="px-3 py-4"><?= e(format_date($record['borrow_date'])) ?></td>
                            <td class="px-3 py-4"><?= e(format_date($record['due_date'])) ?></td>
                            <td class="px-3 py-4">
                                <span class="badge <?= e(status_badge_class($record['status'])) ?>"><?= e(ucfirst($record['status'])) ?></span>
                            </td>
                            <td class="px-3 py-4"><?= e(format_money((float) $record['penalty'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-5">
        <div class="panel">
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Quick Actions</p>
            <div class="mt-5 grid gap-3">
                <a href="<?= e(url('admin/books/create.php')) ?>" class="btn-primary">Add a New Book</a>
                <a href="<?= e(url('admin/borrowers/create.php')) ?>" class="btn-secondary">Add Borrower</a>
                <a href="<?= e(url('admin/borrowers/index.php')) ?>" class="btn-secondary">Review Borrowers</a>
                <a href="<?= e(url('admin/transactions/borrow.php')) ?>" class="btn-secondary">Create Borrow Record</a>
            </div>
        </div>

        <div class="panel">
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Report Shortcuts</p>
            <div class="mt-5 grid gap-3 text-sm">
                <a href="<?= e(url('admin/reports/borrowed.php')) ?>" class="rounded-2xl border border-slate-200 px-4 py-3 hover:bg-slate-50">Borrowed Books Report</a>
                <a href="<?= e(url('admin/reports/returned.php')) ?>" class="rounded-2xl border border-slate-200 px-4 py-3 hover:bg-slate-50">Returned Books Report</a>
                <a href="<?= e(url('admin/reports/overdue.php')) ?>" class="rounded-2xl border border-slate-200 px-4 py-3 hover:bg-slate-50">Overdue Books Report</a>
                <a href="<?= e(url('admin/reports/most-borrowed.php')) ?>" class="rounded-2xl border border-slate-200 px-4 py-3 hover:bg-slate-50">Most Borrowed Books</a>
            </div>
        </div>
    </div>
</section>
<?php
render_app_end();

