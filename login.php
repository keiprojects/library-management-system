<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

redirect_if_authenticated();

$email = '';
$errors = [];

if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $errors[] = 'Email is required.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if ($errors === []) {
        $user = find_user_by_email($email);

        if ($user !== null && password_verify($password, $user['password'])) {
            if (!user_can_log_in($user)) {
                $errors[] = ($user['role'] ?? '') === 'borrower'
                    ? borrower_login_block_message($user)
                    : 'Your account cannot log in right now.';
            } else {
                login_user($user);

                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                }

                redirect('student/dashboard.php');
            }
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

render_auth_start('Login');
?>
<div class="panel">
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.3em] text-library-ink/50">Welcome Back</p>
        <h2 class="mt-3 text-3xl font-semibold text-library-ink">Log in to your account</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">Use your librarian or borrower account to access the system.</p>
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
            <label for="email" class="label-text">Email Address</label>
            <input type="email" id="email" name="email" class="input-field" value="<?= e($email) ?>" placeholder="student@example.com">
        </div>
        <div>
            <label for="password" class="label-text">Password</label>
            <input type="password" id="password" name="password" class="input-field" placeholder="Enter your password">
        </div>
        <button type="submit" class="btn-primary w-full">Log In</button>
    </form>

    <div class="mt-6 flex flex-col gap-3 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
        <p>Borrower and no account yet?</p>
        <a href="<?= e(url('register.php')) ?>" class="font-semibold text-library-ink">Create a borrower account</a>
    </div>
    <?php if (EMAIL_VERIFICATION_MODE !== 'none'): ?>
        <div class="mt-3 text-sm text-slate-600">
            Need another verification email?
            <a href="<?= e(url('resend-verification.php')) ?>" class="font-semibold text-library-ink">Resend it here</a>
        </div>
    <?php endif; ?>
</div>
<?php
render_auth_end();

