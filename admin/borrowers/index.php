<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$search = trim($_GET['search'] ?? '');
$borrowers = get_borrowers($search);

render_app_start('Borrower Management', 'borrowers');
?>
<section class="panel">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Borrowers</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">View and manage borrower accounts</h3>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <form method="get" class="flex gap-3">
                <input type="text" name="search" value="<?= e($search) ?>" class="input-field min-w-[220px]" placeholder="Search name, email, student ID...">
                <button type="submit" class="btn-secondary">Search</button>
            </form>
            <a href="<?= e(url('admin/borrowers/create.php')) ?>" class="btn-primary">Add Borrower</a>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Student ID</th>
                    <th class="px-3 py-3">Name</th>
                    <th class="px-3 py-3">Email</th>
                    <th class="px-3 py-3">Course / Year</th>
                    <th class="px-3 py-3">Contact</th>
                    <th class="px-3 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($borrowers === []): ?>
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-slate-500">No borrowers found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($borrowers as $borrower): ?>
                    <tr>
                        <td class="px-3 py-4 font-semibold text-library-ink"><?= e($borrower['student_id']) ?></td>
                        <td class="px-3 py-4"><?= e($borrower['name']) ?></td>
                        <td class="px-3 py-4"><?= e($borrower['email']) ?></td>
                        <td class="px-3 py-4"><?= e($borrower['course']) ?> / <?= e($borrower['year_level']) ?></td>
                        <td class="px-3 py-4"><?= e($borrower['contact_info']) ?></td>
                        <td class="px-3 py-4">
                            <a href="<?= e(url('admin/borrowers/edit.php?id=' . $borrower['user_id'])) ?>" class="btn-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_app_end();

