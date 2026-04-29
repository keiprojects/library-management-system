<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$records = get_report_records('overdue', $dateFrom, $dateTo, 'due_date');

render_app_start('Overdue Books Report', 'overdue_report');
?>
<section class="panel">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Reports</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Overdue books report</h3>
        </div>
        <button type="button" onclick="window.print()" class="btn-secondary">Print Report</button>
    </div>

    <form method="get" class="mt-6 grid gap-4 md:grid-cols-3">
        <div>
            <label class="label-text" for="date_from">Date From</label>
            <input type="date" id="date_from" name="date_from" class="input-field" value="<?= e($dateFrom) ?>">
        </div>
        <div>
            <label class="label-text" for="date_to">Date To</label>
            <input type="date" id="date_to" name="date_to" class="input-field" value="<?= e($dateTo) ?>">
        </div>
        <div class="flex items-end gap-3">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="<?= e(url('admin/reports/overdue.php')) ?>" class="btn-secondary">Reset</a>
        </div>
    </form>

    <div class="mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Borrower</th>
                    <th class="px-3 py-3">Book</th>
                    <th class="px-3 py-3">Due Date</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Penalty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($records === []): ?>
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-slate-500">No overdue records found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td class="px-3 py-4"><?= e($record['name']) ?> (<?= e($record['student_id']) ?>)</td>
                        <td class="px-3 py-4"><?= e($record['title']) ?></td>
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
