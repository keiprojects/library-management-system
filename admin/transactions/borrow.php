<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$borrowers = get_borrower_options();
$books = get_books('', true);
$activeRecords = get_active_borrow_records();
$errors = [];
$form = [
    'user_id' => '',
    'book_id' => '',
    'due_date' => date('Y-m-d', strtotime('+7 days')),
];

if (is_post()) {
    $form = [
        'user_id' => trim($_POST['user_id'] ?? ''),
        'book_id' => trim($_POST['book_id'] ?? ''),
        'due_date' => trim($_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days'))),
    ];

    if ($form['user_id'] === '') {
        $errors[] = 'Borrower is required.';
    }

    if ($form['book_id'] === '') {
        $errors[] = 'Book is required.';
    }

    if ($form['due_date'] === '') {
        $errors[] = 'Due date is required.';
    }

    if ($errors === []) {
        $result = borrow_book((int) $form['user_id'], (int) $form['book_id'], $form['due_date']);

        if ($result['success']) {
            flash('success', $result['message']);
            redirect('admin/transactions/borrow.php');
        }

        $errors[] = $result['message'];
    }
}

render_app_start('Borrow Book', 'borrow');
?>
<section class="grid gap-8 xl:grid-cols-[0.9fr_1.1fr]">
    <div class="panel">
        <div class="mb-6">
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Borrow Form</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Create a new borrow record</h3>
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

        <form method="post" class="grid gap-5">
            <div>
                <label class="label-text" for="user_id">Borrower</label>
                <select id="user_id" name="user_id" class="input-field">
                    <option value="">Select borrower</option>
                    <?php foreach ($borrowers as $borrower): ?>
                        <option value="<?= e((string) $borrower['id']) ?>" <?= selected($form['user_id'], (string) $borrower['id']) ?>>
                            <?= e($borrower['name']) ?> (<?= e($borrower['student_id']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label-text" for="book_id">Book</label>
                <select id="book_id" name="book_id" class="input-field">
                    <option value="">Select book</option>
                    <?php foreach ($books as $book): ?>
                        <option value="<?= e((string) $book['id']) ?>" <?= selected($form['book_id'], (string) $book['id']) ?>>
                            <?= e($book['title']) ?> (<?= e((string) $book['available_quantity']) ?> available)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label-text" for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" class="input-field" value="<?= e($form['due_date']) ?>">
            </div>
            <button type="submit" class="btn-primary">Borrow Book</button>
        </form>
    </div>

    <div class="panel overflow-hidden">
        <div class="mb-5">
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Current Records</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Borrowed and overdue books</h3>
        </div>
        <div class="overflow-x-auto">
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
                    <?php if ($activeRecords === []): ?>
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-slate-500">No active borrow records yet.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($activeRecords as $record): ?>
                        <tr>
                            <td class="px-3 py-4">
                                <p class="font-semibold text-library-ink"><?= e($record['name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($record['student_id']) ?></p>
                            </td>
                            <td class="px-3 py-4"><?= e($record['title']) ?></td>
                            <td class="px-3 py-4"><?= e(format_date($record['due_date'])) ?></td>
                            <td class="px-3 py-4"><span class="badge <?= e(status_badge_class($record['status'])) ?>"><?= e(ucfirst($record['status'])) ?></span></td>
                            <td class="px-3 py-4"><?= e(format_money((float) $record['penalty'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php
render_app_end();

