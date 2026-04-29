<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$records = get_most_borrowed_books();

render_app_start('Most Borrowed Books', 'most_borrowed');
?>
<section class="panel">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Reports</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Most borrowed books</h3>
        </div>
        <button type="button" onclick="window.print()" class="btn-secondary">Print Report</button>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Title</th>
                    <th class="px-3 py-3">Author</th>
                    <th class="px-3 py-3">Category</th>
                    <th class="px-3 py-3">Borrow Count</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($records === []): ?>
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-slate-500">No books found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td class="px-3 py-4 font-semibold text-library-ink"><?= e($record['title']) ?></td>
                        <td class="px-3 py-4"><?= e($record['author']) ?></td>
                        <td class="px-3 py-4"><?= e($record['category']) ?></td>
                        <td class="px-3 py-4"><?= e((string) $record['borrow_count']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_app_end();

