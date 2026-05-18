<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$search = trim($_GET['search'] ?? '');

if (is_post()) {
    $recordId = (int) ($_POST['record_id'] ?? 0);
    $result = return_book($recordId);
    flash($result['success'] ? 'success' : 'error', $result['message']);
    redirect('admin/transactions/return.php');
}

$activeRecords = get_active_borrow_records($search);

render_app_start('Return Book', 'return');
?>
<section class="panel overflow-hidden">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Returns</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Manage book returns</h3>
        </div>
        <form method="get" class="flex gap-3">
            <input type="text" name="search" value="<?= e($search) ?>" class="input-field min-w-[220px]" placeholder="Search borrower or book...">
            <button type="submit" class="btn-secondary">Search</button>
        </form>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Borrower</th>
                    <th class="px-3 py-3">Book</th>
                    <th class="px-3 py-3">Borrow Date</th>
                    <th class="px-3 py-3">Due Date</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Penalty</th>
                    <th class="px-3 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($activeRecords === []): ?>
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">No active records found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($activeRecords as $record): ?>
                    <tr>
                        <td class="px-3 py-4">
                            <p class="font-semibold text-library-ink"><?= e($record['name']) ?></p>
                            <p class="text-xs text-slate-500"><?= e($record['student_id']) ?></p>
                        </td>
                        <td class="px-3 py-4"><?= e($record['title']) ?></td>
                        <td class="px-3 py-4"><?= e(format_date($record['borrow_date'])) ?></td>
                        <td class="px-3 py-4"><?= e(format_datetime($record['due_date'])) ?></td>
                        <td class="px-3 py-4"><span class="badge <?= e(status_badge_class($record['status'])) ?>"><?= e(ucfirst($record['status'])) ?></span></td>
                        <td class="px-3 py-4"><?= e(format_money((float) $record['penalty'])) ?></td>
                        <td class="px-3 py-4">
                            <form method="post">
                                <input type="hidden" name="record_id" value="<?= e((string) $record['id']) ?>">
                                <button type="submit" class="btn-primary" data-confirm="Mark this book as returned?">Return Book</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_app_end();

