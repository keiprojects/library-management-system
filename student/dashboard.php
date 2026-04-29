<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_borrower();

$user = current_user();
$stats = get_borrower_dashboard_stats((int) $user['id']);
$currentRecords = get_borrower_current_records((int) $user['id']);

render_app_start('Student Dashboard', 'dashboard');
?>
<section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Available Books</p>
        <p class="mt-4 text-4xl font-semibold text-library-ink"><?= e((string) $stats['available_books']) ?></p>
        <p class="mt-2 text-sm text-slate-500">Copies that are still available in the library.</p>
    </div>
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Active Loans</p>
        <p class="mt-4 text-4xl font-semibold text-library-ink"><?= e((string) $stats['active_loans']) ?></p>
        <p class="mt-2 text-sm text-slate-500">Books you are currently borrowing.</p>
    </div>
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Overdue</p>
        <p class="mt-4 text-4xl font-semibold text-amber-700"><?= e((string) $stats['overdue_loans']) ?></p>
        <p class="mt-2 text-sm text-slate-500">Active records that already passed the due date.</p>
    </div>
    <div class="stat-block">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Returned Books</p>
        <p class="mt-4 text-4xl font-semibold text-library-ink"><?= e((string) $stats['returned_books']) ?></p>
        <p class="mt-2 text-sm text-slate-500">Completed borrow transactions in your account.</p>
    </div>
</section>

<section class="mt-8 panel overflow-hidden">
    <div class="mb-5 flex items-center justify-between gap-4">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Current Loans</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Books currently borrowed</h3>
        </div>
        <a href="<?= e(url('student/books.php')) ?>" class="btn-secondary">Browse Books</a>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Book</th>
                    <th class="px-3 py-3">Category</th>
                    <th class="px-3 py-3">Borrow Date</th>
                    <th class="px-3 py-3">Due Date</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Penalty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($currentRecords === []): ?>
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-slate-500">You do not have any active borrowed books.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($currentRecords as $record): ?>
                    <tr>
                        <td class="px-3 py-4">
                            <p class="font-semibold text-library-ink"><?= e($record['title']) ?></p>
                            <p class="text-xs text-slate-500"><?= e($record['author']) ?></p>
                        </td>
                        <td class="px-3 py-4"><?= e($record['category']) ?></td>
                        <td class="px-3 py-4"><?= e(format_date($record['borrow_date'])) ?></td>
                        <td class="px-3 py-4"><?= e(format_date($record['due_date'])) ?></td>
                        <td class="px-3 py-4"><span class="badge <?= e(status_badge_class($record['status'])) ?>"><?= e(ucfirst($record['status'])) ?></span></td>
                        <td class="px-3 py-4"><?= e(format_money((float) $record['penalty'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_app_end();
