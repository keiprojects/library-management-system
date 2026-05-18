<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_borrower();

$search = trim($_GET['search'] ?? '');
$currentUser = current_user();

if (is_post()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_to_cart') {
        $result = add_book_to_cart((int) $currentUser['id'], (int) ($_POST['book_id'] ?? 0));
        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect('student/books.php?search=' . urlencode($search));
    }

    if ($action === 'remove_from_cart') {
        remove_book_from_cart((int) $currentUser['id'], (int) ($_POST['cart_item_id'] ?? 0));
        flash('success', 'Book removed from reservation cart.');
        redirect('student/books.php?search=' . urlencode($search));
    }
}

$books = get_books($search, true);
$cartItems = get_reservation_cart_items((int) $currentUser['id']);

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
                        <th class="px-3 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($books === []): ?>
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-slate-500">No available books found.</td>
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
                        <td class="px-3 py-4">
                            <form method="post">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="book_id" value="<?= e((string) $book['id']) ?>">
                                <button type="submit" class="btn-secondary">Add to Cart</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<section class="panel mt-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Reservation Cart</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Books to reserve</h3>
        </div>
        <span class="badge bg-slate-100 text-slate-700"><?= e((string) count($cartItems)) ?> item(s)</span>
    </div>
    <div class="mt-4 grid gap-3">
        <?php if ($cartItems === []): ?>
            <p class="text-sm text-slate-500">No books in your cart yet.</p>
        <?php endif; ?>
        <?php foreach ($cartItems as $item): ?>
            <div class="rounded-2xl border border-slate-200 px-4 py-3 flex items-center justify-between gap-4">
                <div>
                    <p class="font-semibold text-library-ink"><?= e($item['title']) ?></p>
                    <p class="text-sm text-slate-500"><?= e($item['author']) ?> • <?= e($item['isbn']) ?></p>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="remove_from_cart">
                    <input type="hidden" name="cart_item_id" value="<?= e((string) $item['id']) ?>">
                    <button type="submit" class="btn-danger">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php
render_app_end();
