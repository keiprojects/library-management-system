<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_borrower();

$currentUser = current_user();

if (is_post()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'remove_from_cart') {
        remove_book_from_cart((int) $currentUser['id'], (int) ($_POST['cart_item_id'] ?? 0));
        flash('success', 'Book removed from reservation cart.');
        redirect('student/cart.php');
    }
}

$cartItems = get_student_reservation_items((int) $currentUser['id']);
$pendingCount = count(array_filter($cartItems, static fn (array $item): bool => $item['status'] === 'in_cart'));

render_app_start('Reservation Cart', 'cart');
?>
<section class="panel overflow-hidden">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Reservation Cart</p>
            <h3 class="mt-2 text-2xl font-semibold text-library-ink">View your reserved books</h3>
            <p class="mt-2 text-sm text-slate-500">Review pending reservations with the return date and time you selected.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <span class="badge bg-slate-100 text-slate-700"><?= e((string) $pendingCount) ?> pending item(s)</span>
            <a href="<?= e(url('student/books.php')) ?>" class="btn-secondary">Add More Books</a>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                    <th class="px-3 py-3">Book</th>
                    <th class="px-3 py-3">Category</th>
                    <th class="px-3 py-3">Requested Return</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Added</th>
                    <th class="px-3 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($cartItems === []): ?>
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-slate-500">Your reservation cart is empty.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td class="px-3 py-4">
                            <p class="font-semibold text-library-ink"><?= e($item['title']) ?></p>
                            <p class="text-xs text-slate-500"><?= e($item['author']) ?> • <?= e($item['isbn']) ?></p>
                        </td>
                        <td class="px-3 py-4"><?= e($item['category']) ?></td>
                        <td class="px-3 py-4"><?= e(format_datetime($item['due_date'])) ?></td>
                        <td class="px-3 py-4">
                            <span class="badge <?= e($item['status'] === 'reserved' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800') ?>">
                                <?= e($item['status'] === 'reserved' ? 'Approved' : 'Pending') ?>
                            </span>
                        </td>
                        <td class="px-3 py-4"><?= e(format_datetime((string) $item['created_at'])) ?></td>
                        <td class="px-3 py-4">
                            <?php if ($item['status'] === 'in_cart'): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="remove_from_cart">
                                    <input type="hidden" name="cart_item_id" value="<?= e((string) $item['id']) ?>">
                                    <button type="submit" class="btn-danger">Remove</button>
                                </form>
                            <?php else: ?>
                                <span class="text-sm text-slate-500">Converted to borrowed book</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_app_end();
