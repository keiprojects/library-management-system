<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$search = trim($_GET['search'] ?? '');

if (is_post()) {
    $bookId = (int) ($_POST['book_id'] ?? 0);
    $result = delete_book($bookId);
    flash($result['success'] ? 'success' : 'error', $result['message']);
    redirect('admin/books/index.php');
}

$books = get_books($search);

render_app_start('Book Management', 'books');
?>
<section class="panel">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Inventory</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Manage library books</h3>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <form method="get" class="flex gap-3">
                <input type="text" name="search" value="<?= e($search) ?>" class="input-field min-w-[220px]" placeholder="Search title, author, ISBN...">
                <button type="submit" class="btn-secondary">Search</button>
            </form>
            <a href="<?= e(url('admin/books/create.php')) ?>" class="btn-primary">Add Book</a>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Title</th>
                    <th class="px-3 py-3">Author</th>
                    <th class="px-3 py-3">ISBN</th>
                    <th class="px-3 py-3">Category</th>
                    <th class="px-3 py-3">Quantity</th>
                    <th class="px-3 py-3">Available</th>
                    <th class="px-3 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($books === []): ?>
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">No books found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td class="px-3 py-4 font-semibold text-library-ink"><?= e($book['title']) ?></td>
                        <td class="px-3 py-4"><?= e($book['author']) ?></td>
                        <td class="px-3 py-4"><?= e($book['isbn']) ?></td>
                        <td class="px-3 py-4"><?= e($book['category']) ?></td>
                        <td class="px-3 py-4"><?= e((string) $book['quantity']) ?></td>
                        <td class="px-3 py-4">
                            <span class="badge <?= (int) $book['available_quantity'] > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                <?= e((string) $book['available_quantity']) ?>
                            </span>
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex flex-wrap gap-2">
                                <a href="<?= e(url('admin/books/edit.php?id=' . $book['id'])) ?>" class="btn-secondary">Edit</a>
                                <form method="post">
                                    <input type="hidden" name="book_id" value="<?= e((string) $book['id']) ?>">
                                    <button type="submit" class="btn-danger" data-confirm="Delete this book from the library list?">Delete</button>
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

