<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_borrower();

$search = trim($_GET['search'] ?? '');
$books = get_books($search, true);

render_app_start('Available Books', 'books');
?>
<section class="panel">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Library Catalog</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Browse available books</h3>
        </div>
        <form method="get" class="flex gap-3">
            <input type="text" name="search" value="<?= e($search) ?>" class="input-field min-w-[220px]" placeholder="Search title, author, category...">
            <button type="submit" class="btn-secondary">Search</button>
        </form>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Title</th>
                    <th class="px-3 py-3">Author</th>
                    <th class="px-3 py-3">Category</th>
                    <th class="px-3 py-3">ISBN</th>
                    <th class="px-3 py-3">Available Copies</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($books === []): ?>
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-slate-500">No available books found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td class="px-3 py-4 font-semibold text-library-ink"><?= e($book['title']) ?></td>
                        <td class="px-3 py-4"><?= e($book['author']) ?></td>
                        <td class="px-3 py-4"><?= e($book['category']) ?></td>
                        <td class="px-3 py-4"><?= e($book['isbn']) ?></td>
                        <td class="px-3 py-4">
                            <span class="badge bg-emerald-100 text-emerald-700"><?= e((string) $book['available_quantity']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_app_end();

