<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$form = [
    'name' => '',
    'email' => '',
    'student_id' => '',
    'course' => '',
    'year_level' => '',
    'contact_info' => '',
];
$errors = [];

if (is_post()) {
    $form = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'student_id' => trim($_POST['student_id'] ?? ''),
        'course' => trim($_POST['course'] ?? ''),
        'year_level' => trim($_POST['year_level'] ?? ''),
        'contact_info' => trim($_POST['contact_info'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';

    foreach ($form as $label => $value) {
        if ($value === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $label)) . ' is required.';
        }
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (!value_in_options($form['course'], course_options())) {
        $errors[] = 'Please choose a valid course.';
    }

    if (!value_in_options($form['year_level'], year_level_options())) {
        $errors[] = 'Please choose a valid year level.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($errors === []) {
        $result = create_borrower_account($form + [
            'password' => $password,
            'mark_verified' => true,
        ]);

        if ($result['success']) {
            flash('success', $result['message']);
            redirect('admin/borrowers/index.php');
        }

        $errors[] = $result['message'];
    }
}

render_app_start('Add Borrower', 'borrowers');
?>
<section class="panel max-w-4xl">
    <div class="mb-6">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Borrower Form</p>
        <h3 class="mt-2 text-2xl font-semibold text-library-ink">Create a borrower account</h3>
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
            <label for="name" class="label-text">Full Name</label>
            <input type="text" id="name" name="name" class="input-field" value="<?= e($form['name']) ?>">
        </div>
        <div>
            <label for="email" class="label-text">Email Address</label>
            <input type="email" id="email" name="email" class="input-field" value="<?= e($form['email']) ?>">
        </div>
        <div>
            <label for="student_id" class="label-text">Student ID</label>
            <input type="text" id="student_id" name="student_id" class="input-field" value="<?= e($form['student_id']) ?>">
        </div>
        <div>
            <label for="course" class="label-text">Course</label>
            <select id="course" name="course" class="input-field">
                <option value="">Select a course</option>
                <?php foreach (course_options() as $course): ?>
                    <option value="<?= e($course) ?>" <?= selected($form['course'], $course) ?>><?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="year_level" class="label-text">Year Level</label>
            <select id="year_level" name="year_level" class="input-field">
                <option value="">Select a year level</option>
                <?php foreach (year_level_options() as $yearLevel): ?>
                    <option value="<?= e($yearLevel) ?>" <?= selected($form['year_level'], $yearLevel) ?>><?= e($yearLevel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="contact_info" class="label-text">Contact Information</label>
            <input type="text" id="contact_info" name="contact_info" class="input-field" value="<?= e($form['contact_info']) ?>">
        </div>
        <div class="md:col-span-2">
            <label for="password" class="label-text">Temporary Password</label>
            <input type="password" id="password" name="password" class="input-field" placeholder="The borrower can use this when logging in">
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Save Borrower</button>
            <a href="<?= e(url('admin/borrowers/index.php')) ?>" class="btn-secondary">Back to Borrowers</a>
        </div>
    </form>
</section>
<?php
render_app_end();

