<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$bookId = (int) ($_GET['id'] ?? 0);
$book = get_book($bookId);

if ($book === null) {
    flash('error', 'Book not found.');
    redirect('admin/books/index.php');
}

$form = [
    'title' => $book['title'],
    'author' => $book['author'],
    'isbn' => $book['isbn'],
    'category' => $book['category'],
    'quantity' => (string) $book['quantity'],
];
$errors = [];

if (is_post()) {
    $form = [
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'isbn' => trim($_POST['isbn'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'quantity' => trim($_POST['quantity'] ?? '1'),
    ];

    foreach (['title', 'author', 'isbn', 'category', 'quantity'] as $field) {
        if ($form[$field] === '') {
            $errors[] = ucfirst($field) . ' is required.';
        }
    }

    if ((int) $form['quantity'] < 1) {
        $errors[] = 'Quantity must be at least 1.';
    }

    if ($errors === []) {
        $result = update_book($bookId, [
            'title' => $form['title'],
            'author' => $form['author'],
            'isbn' => $form['isbn'],
            'category' => $form['category'],
            'quantity' => (int) $form['quantity'],
        ]);

        if ($result['success']) {
            flash('success', $result['message']);
            redirect('admin/books/index.php');
        }

        $errors[] = $result['message'];
    }
}

render_app_start('Edit Book', 'books');
?>
<section class="panel max-w-4xl">
    <div class="mb-6">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Book Form</p>
        <h3 class="mt-2 text-2xl font-semibold text-library-ink">Edit book details</h3>
        <p class="mt-2 text-sm text-slate-500">Available quantity is recalculated based on copies that are still borrowed.</p>
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

    <form method="post" class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="label-text" for="title">Book Title</label>
            <input class="input-field" id="title" name="title" type="text" value="<?= e($form['title']) ?>">
        </div>
        <div>
            <label class="label-text" for="author">Author</label>
            <input class="input-field" id="author" name="author" type="text" value="<?= e($form['author']) ?>">
        </div>
        <div>
            <label class="label-text" for="isbn">ISBN</label>
            <input class="input-field" id="isbn" name="isbn" type="text" value="<?= e($form['isbn']) ?>">
        </div>
        <div>
            <label class="label-text" for="category">Category</label>
            <input class="input-field" id="category" name="category" type="text" value="<?= e($form['category']) ?>">
        </div>
        <div>
            <label class="label-text" for="quantity">Quantity</label>
            <input class="input-field" id="quantity" name="quantity" type="number" min="1" value="<?= e($form['quantity']) ?>">
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Update Book</button>
            <a href="<?= e(url('admin/books/index.php')) ?>" class="btn-secondary">Back to Books</a>
        </div>
    </form>
</section>
<?php
render_app_end();

