<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

redirect_if_authenticated();

$email = '';
$errors = [];

if (is_post()) {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if ($errors === []) {
        $user = find_user_by_email($email);

        if ($user === null || ($user['role'] ?? '') !== 'borrower') {
            $errors[] = 'No borrower account was found for that email address.';
        } elseif (!email_requires_verification((string) $user['email'])) {
            $errors[] = 'That borrower account does not require email verification in the current setup.';
        } elseif (!empty($user['email_verified_at'])) {
            flash('success', 'That borrower account is already verified. You can log in now.');
            redirect('login.php');
        } else {
            $tokenResult = issue_email_verification((int) $user['id']);

            if (!$tokenResult['success'] || $tokenResult['token'] === null) {
                $errors[] = $tokenResult['message'];
            } else {
                $mailResult = send_verification_email($user, $tokenResult['token']);

                if ($mailResult['success']) {
                    flash('success', 'A new verification email has been sent.');
                    redirect('login.php');
                }

                $errors[] = 'Unable to send the verification email. ' . $mailResult['message'];
            }
        }
    }
}

render_auth_start('Resend Verification');
?>
<div class="panel">
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.3em] text-library-ink/50">Borrower Verification</p>
        <h2 class="mt-3 text-3xl font-semibold text-library-ink">Resend verification email</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">Use this form if a borrower did not receive the original verification email.</p>
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
            <input type="email" id="email" name="email" class="input-field" value="<?= e($email) ?>" placeholder="borrower@school.edu">
        </div>
        <button type="submit" class="btn-primary w-full">Send Verification Email Again</button>
    </form>

    <div class="mt-6 text-sm text-slate-600">
        Already ready to log in?
        <a href="<?= e(url('login.php')) ?>" class="font-semibold text-library-ink">Back to login</a>
    </div>
</div>
<?php
render_auth_end();
