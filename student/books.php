<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_borrower();

$search = trim($_GET['search'] ?? '');
$currentUser = current_user();
$defaultReturnDate = date('Y-m-d\TH:i', strtotime('+7 days'));

if (is_post()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_to_cart') {
        $result = add_book_to_cart(
            (int) $currentUser['id'],
            (int) ($_POST['book_id'] ?? 0),
            trim($_POST['due_date'] ?? '')
        );
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
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="<?= e(url('student/cart.php')) ?>" class="btn-primary">View Cart (<?= e((string) count($cartItems)) ?>)</a>
            <form method="get" class="flex gap-3">
                <input type="text" name="search" value="<?= e($search) ?>" class="input-field min-w-[220px]" placeholder="Search title, author, category...">
                <button type="submit" class="btn-secondary">Search</button>
            </form>
        </div>
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
                            <form method="post" class="grid gap-2 min-w-[220px]">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="book_id" value="<?= e((string) $book['id']) ?>">
                                <label class="sr-only" for="due_date_<?= e((string) $book['id']) ?>">Return date and time</label>
                                <input type="datetime-local" id="due_date_<?= e((string) $book['id']) ?>" name="due_date" class="input-field !py-2" value="<?= e($defaultReturnDate) ?>" min="<?= e(date('Y-m-d\TH:i')) ?>" required>
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
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Reservation Cart</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">Ready to review your selected books?</h3>
            <p class="mt-2 text-sm text-slate-500">Open your cart to view reserved books, requested return times, and remove pending items.</p>
        </div>
        <a href="<?= e(url('student/cart.php')) ?>" class="btn-primary">View Cart (<?= e((string) count($cartItems)) ?>)</a>
    </div>
</section>
<?php
render_app_end();
