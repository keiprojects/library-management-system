<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

redirect_if_authenticated();

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
    $confirmPassword = $_POST['confirm_password'] ?? '';

    foreach ($form as $label => $value) {
        if ($value === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $label)) . ' is required.';
        }
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    if ($errors === []) {
        $result = create_borrower_account($form + ['password' => $password]);

        if ($result['success']) {
            flash('success', 'Registration successful. You can now log in.');
            redirect('login.php');
        }

        $errors[] = $result['message'];
    }
}

render_auth_start('Register');
?>
<div class="panel">
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.3em] text-library-ink/50">Borrower Registration</p>
        <h2 class="mt-3 text-3xl font-semibold text-library-ink">Create a borrower account</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">Students can sign up here to browse books and monitor borrowed items.</p>
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
            <input type="text" id="name" name="name" class="input-field" value="<?= e($form['name']) ?>" placeholder="Juan Dela Cruz">
        </div>
        <div>
            <label for="email" class="label-text">Email Address</label>
            <input type="email" id="email" name="email" class="input-field" value="<?= e($form['email']) ?>" placeholder="student@example.com">
        </div>
        <div>
            <label for="student_id" class="label-text">Student ID</label>
            <input type="text" id="student_id" name="student_id" class="input-field" value="<?= e($form['student_id']) ?>" placeholder="2024-00001">
        </div>
        <div>
            <label for="course" class="label-text">Course</label>
            <input type="text" id="course" name="course" class="input-field" value="<?= e($form['course']) ?>" placeholder="BS Information Technology">
        </div>
        <div>
            <label for="year_level" class="label-text">Year Level</label>
            <input type="text" id="year_level" name="year_level" class="input-field" value="<?= e($form['year_level']) ?>" placeholder="3rd Year">
        </div>
        <div class="md:col-span-2">
            <label for="contact_info" class="label-text">Contact Information</label>
            <input type="text" id="contact_info" name="contact_info" class="input-field" value="<?= e($form['contact_info']) ?>" placeholder="09xx xxx xxxx">
        </div>
        <div>
            <label for="password" class="label-text">Password</label>
            <input type="password" id="password" name="password" class="input-field" placeholder="At least 6 characters">
        </div>
        <div>
            <label for="confirm_password" class="label-text">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="input-field" placeholder="Repeat your password">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-primary w-full">Register Borrower Account</button>
        </div>
    </form>

    <div class="mt-6 text-sm text-slate-600">
        Already have an account?
        <a href="<?= e(url('login.php')) ?>" class="font-semibold text-library-ink">Log in here</a>
    </div>
</div>
<?php
render_auth_end();

