<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_borrower();

$user = current_user();
$history = get_borrower_history((int) $user['id']);

render_app_start('Returned History', 'history');
?>
<section class="panel overflow-hidden">
    <div class="mb-5">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">History</p>
        <h3 class="mt-2 text-2xl font-semibold text-library-ink">Returned books history</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Book</th>
                    <th class="px-3 py-3">Borrow Date</th>
                    <th class="px-3 py-3">Due Date</th>
                    <th class="px-3 py-3">Return Date</th>
                    <th class="px-3 py-3">Penalty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($history === []): ?>
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-slate-500">No returned books yet.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($history as $record): ?>
                    <tr>
                        <td class="px-3 py-4">
                            <p class="font-semibold text-library-ink"><?= e($record['title']) ?></p>
                            <p class="text-xs text-slate-500"><?= e($record['author']) ?></p>
                        </td>
                        <td class="px-3 py-4"><?= e(format_date($record['borrow_date'])) ?></td>
                        <td class="px-3 py-4"><?= e(format_datetime($record['due_date'])) ?></td>
                        <td class="px-3 py-4"><?= e(format_datetime($record['return_date'])) ?></td>
                        <td class="px-3 py-4"><?= e(format_money((float) $record['penalty'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_app_end();

